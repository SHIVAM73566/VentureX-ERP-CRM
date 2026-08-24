/**
 * VentureX Sync Engine — pushes Shopify orders & products into VentureX ERP
 *
 * Drop-in location:  venturex-sync/app/services/venturex-sync.server.js
 *
 * Uses your VentureX REST API (18 controllers shipped with the product).
 * Configure once via .env:
 *   VENTUREX_API_URL=https://your-domain.com/api
 *   VENTUREX_API_TOKEN=xxxx   (VentureX > Settings > API > generate token)
 */

const VENTUREX_API_URL = process.env.VENTUREX_API_URL || "";
const VENTUREX_API_TOKEN = process.env.VENTUREX_API_TOKEN || "";

function headers() {
  return {
    "Content-Type": "application/json",
    Accept: "application/json",
    Authorization: `Bearer ${VENTUREX_API_TOKEN}`,
  };
}

async function post(path, body) {
  if (!VENTUREX_API_URL) throw new Error("VENTUREX_API_URL not configured");
  const res = await fetch(`${VENTUREX_API_URL}${path}`, {
    method: "POST",
    headers: headers(),
    body: JSON.stringify(body),
  });
  if (!res.ok) {
    const text = await res.text().catch(() => "");
    throw new Error(`VentureX ${path} failed (${res.status}): ${text.slice(0, 200)}`);
  }
  return res.json().catch(() => ({}));
}

/**
 * orders/create webhook -> VentureX Sales Order
 * Maps Shopify line items onto a VentureX sales-order payload.
 */
export async function syncOrder(shopDomain, order) {
  return post("/sales-orders", {
    source: "shopify",
    external_store: shopDomain,
    external_id: String(order.id),
    reference: order.name ?? `SHOPIFY-${order.id}`,
    status: mapFinancialStatus(order.financial_status),
    currency: order.currency,
    ordered_at: order.created_at,
    customer: {
      name: [order.customer?.first_name, order.customer?.last_name].filter(Boolean).join(" ") || "Guest",
      email: order.customer?.email ?? order.email ?? null,
      phone: order.customer?.phone ?? order.phone ?? null,
    },
    items: (order.line_items ?? []).map((li) => ({
      sku: li.sku ?? `SHOPIFY-${li.product_id}`,
      name: li.title,
      quantity: Number(li.quantity),
      unit_price: Number(li.price),
    })),
    totals: {
      subtotal: Number(order.subtotal_price ?? 0),
      shipping: Number(order.total_shipping_price_set?.shop_money?.amount ?? 0),
      tax: Number(order.total_tax ?? 0),
      grand_total: Number(order.total_price ?? 0),
    },
  });
}

/**
 * products/create | products/update -> VentureX Product catalog
 */
export async function syncProduct(shopDomain, product) {
  const firstVariant = product.variants?.[0] ?? {};
  return post("/products", {
    source: "shopify",
    external_store: shopDomain,
    external_id: String(product.id),
    name: product.title,
    description: (product.body_html ?? "").replace(/<[^>]+>/g, "").slice(0, 5000),
    sku: firstVariant.sku ?? `SHOPIFY-${product.id}`,
    price: Number(firstVariant.price ?? 0),
    stock_quantity: Number(firstVariant.inventory_quantity ?? 0),
    status: product.status === "active" ? "published" : "draft",
    image_url: product.image?.src ?? null,
  });
}

function mapFinancialStatus(s) {
  switch (s) {
    case "paid": return "confirmed";
    case "pending": return "pending";
    case "refunded":
    case "voided": return "cancelled";
    default: return "processing";
  }
}
