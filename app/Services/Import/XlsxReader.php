<?php

declare(strict_types=1);

namespace App\Services\Import;

use InvalidArgumentException;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

/**
 * Minimal XLSX reader built only on native PHP extensions (ZipArchive +
 * SimpleXML). It reads the first worksheet of an .xlsx workbook and returns
 * the same row/header array shape as the CSV parser.
 */
class XlsxReader
{
    private const MAIN_NS = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /**
     * @return list<array<string, string|null>>
     */
    public function read(string $path): array
    {
        if (! class_exists(ZipArchive::class) || ! class_exists(SimpleXMLElement::class)) {
            throw new RuntimeException('XLSX imports require the zip and xml PHP extensions.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            throw new InvalidArgumentException('The XLSX file could not be opened.');
        }

        $sharedStrings = $this->readSharedStrings($zip);
        $sheetPath = $this->firstWorksheet($zip);

        if ($sheetPath === null) {
            $zip->close();
            throw new InvalidArgumentException('The XLSX file does not contain a worksheet.');
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();

        if (! is_string($sheetXml) || $sheetXml === '') {
            throw new InvalidArgumentException('The XLSX worksheet is empty or unreadable.');
        }

        return $this->rowsFromXml($sheetXml, $sharedStrings);
    }

    /**
     * @return list<string>
     */
    protected function readSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if (! is_string($xml) || $xml === '') {
            return [];
        }

        $document = $this->parseXml($xml);
        $document->registerXPathNamespace('m', self::MAIN_NS);

        $strings = [];
        foreach ($document->xpath('//m:si') ?: [] as $item) {
            $item->registerXPathNamespace('m', self::MAIN_NS);
            $parts = [];
            foreach ($item->xpath('.//m:t') ?: [] as $text) {
                $parts[] = (string) $text;
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    protected function firstWorksheet(ZipArchive $zip): ?string
    {
        if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
            return 'xl/worksheets/sheet1.xml';
        }

        $candidates = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name) === 1) {
                $candidates[] = $name;
            }
        }

        if ($candidates === []) {
            return null;
        }

        sort($candidates, SORT_NATURAL);

        return $candidates[0];
    }

    /**
     * @param  list<string>  $sharedStrings
     * @return list<array<string, string|null>>
     */
    protected function rowsFromXml(string $xml, array $sharedStrings): array
    {
        $document = $this->parseXml($xml);
        $document->registerXPathNamespace('m', self::MAIN_NS);

        $rawRows = [];
        foreach ($document->xpath('//m:sheetData/m:row') ?: [] as $row) {
            $row->registerXPathNamespace('m', self::MAIN_NS);

            $cells = [];
            foreach ($row->xpath('./m:c') ?: [] as $cell) {
                $reference = (string) $cell['r'];
                $index = $this->columnIndex($reference);
                $cells[$index] = $this->cellValue($cell, $sharedStrings);
            }

            if ($cells === []) {
                continue;
            }

            $maxIndex = max(array_keys($cells));
            $values = [];
            for ($i = 0; $i <= $maxIndex; $i++) {
                $values[] = $cells[$i] ?? null;
            }

            if ($this->isEmptyRow($values)) {
                continue;
            }

            $rawRows[] = $values;
        }

        if (count($rawRows) < 2) {
            return [];
        }

        $headers = $this->normalizeHeaders(array_shift($rawRows));
        $rows = [];

        foreach ($rawRows as $values) {
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = $values[$index] ?? null;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param  array<int, string|null>  $headers
     * @return array<int, string>
     */
    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        $seen = [];

        foreach ($headers as $index => $header) {
            $value = trim((string) $header);
            if ($value === '') {
                $value = 'column_'.($index + 1);
            }

            $key = preg_replace('/[^a-zA-Z0-9_]+/', '_', $value) ?: $value;
            $key = trim($key, '_');
            if ($key === '') {
                $key = 'column_'.($index + 1);
            }

            if (isset($seen[$key])) {
                $seen[$key]++;
                $key .= '_'.$seen[$key];
            } else {
                $seen[$key] = 1;
            }

            $normalized[$index] = $key;
        }

        return $normalized;
    }

    /**
     * @param  array<int, string|null>  $values
     */
    protected function isEmptyRow(array $values): bool
    {
        foreach ($values as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $sharedStrings
     */
    protected function cellValue(SimpleXMLElement $cell, array $sharedStrings): ?string
    {
        $type = (string) $cell['t'];

        if ($type === 'inlineStr') {
            $parts = [];
            foreach ($cell->children(self::MAIN_NS)->is->t ?? [] as $text) {
                $parts[] = (string) $text;
            }

            return implode('', $parts);
        }

        $value = $cell->children(self::MAIN_NS)->v ?? null;
        if ($value === null) {
            return null;
        }

        $raw = (string) $value;
        if ($raw === '') {
            return null;
        }

        return match ($type) {
            's' => $sharedStrings[(int) $raw] ?? null,
            'b' => $raw === '1' ? 'TRUE' : 'FALSE',
            default => $raw,
        };
    }

    protected function columnIndex(string $reference): int
    {
        $letters = strtoupper(preg_replace('/[^A-Z]/i', '', $reference) ?: '');
        if ($letters === '') {
            return 0;
        }

        $index = 0;
        $length = strlen($letters);
        for ($i = 0; $i < $length; $i++) {
            $index = ($index * 26) + (ord($letters[$i]) - 64);
        }

        return $index - 1;
    }

    protected function parseXml(string $xml): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($document === false) {
            throw new InvalidArgumentException('The XLSX worksheet contains invalid XML.');
        }

        return $document;
    }
}
