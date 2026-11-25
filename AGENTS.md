# VentureX ERP & CRM — Permanent Project Rules

These rules apply to every future development session in this repository.

## 1. Absolute UI and UX preservation

Preserve 100% of the existing application appearance and behavior:

- Layout, page structure, dashboard, sidebar, header, footer.
- Menus, dropdowns, buttons, cards, widgets, tables, forms.
- Colors, backgrounds, typography, spacing, icons, animations, responsive behavior.
- Existing workflows, branding, and visual identity.

Do not redesign pages, move controls, replace working components, introduce a new design system, or change CSS without a verified technical necessity.

Frontend changes are permitted only when technically required to connect a backend feature. If a visual change is unavoidable, stop, identify the file, explain why, describe the possible visual impact, and wait for approval.

## 2. No full rewrites

Work surgically:

1. Inspect before editing.
2. Change only the minimum files required.
3. Reuse existing utilities, services, components, and APIs.
4. Preserve function signatures, interfaces, and component hierarchy.
5. Do not delete working code without proving it defective or unsafe.
6. Maintain backward compatibility and existing customer data.

If a file needs significant modification, explain why before replacing its contents.

## 3. Baseline before changes

Before implementation:

- Check Git status and preserve user changes.
- Record current build, test, lint, and type-check results.
- Distinguish pre-existing failures from newly introduced failures.
- Never claim existing failures were caused by new changes.

## 4. Security and secret handling

- Never print, echo, log, or commit `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PRICE_*`, database credentials, API tokens, or customer data.
- Report only `configured` / `missing` and mode.
- Keep `.env` and `.env.bak-*` ignored by Git.
- Never expose server environment values to Blade, JavaScript, logs, or error responses.
- Use verified server-side Stripe events only. Never unlock features from a browser success message.
- Do not claim absolute security, unhackability, SOC 2 compliance, HIPAA compliance, or end-to-end encryption without proper evidence and independent validation.

## 5. Data protection

- Never reset the database or delete customer, CRM, sales, finance, HR, or ERP records.
- Use backward-compatible migrations and test them before production.
- Cancellation or failed payment may only adjust paid add-on entitlement status.
- Create rollback procedures for schema changes.

## 6. Stripe and AppSumo integrity

Keep AppSumo rules unchanged:

- 1 Code = $49.
- 2 Codes Stacked = $98.
- Original/Direct comparison = $499.
- Maximum 2 stackable codes.
- Card-free redemption.

Stripe add-ons are separate paid entitlements. Preserve the existing pricing page design and checkout experience.

## 7. Change control

For every task:

1. Identify affected files and reasons.
2. Make minimal changes.
3. Run relevant tests.
4. Inspect the Git diff.
5. Confirm unrelated functionality is unchanged.
6. Document remaining issues.
7. Do not silently make broad architectural changes.
8. Do not automatically deploy to production.

## 8. Verification standard

Never fabricate success. Only report a check as passed when the actual command and output are recorded. Distinguish build success, syntax success, test success, lint success, and runtime verification.
