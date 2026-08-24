/**
 * Mandatory GDPR compliance webhook handlers.
 *
 * Drop-in location: venturex-sync/app/services/gdpr.server.js
 *
 * Shopify REJECTS App Store submissions that do not correctly implement:
 *   - customers/data_request
 *   - customers/redact
 *   - shop/redact
 *
 * These handlers forward deletion/data requests to your VentureX install
 * so customer data stays compliant across BOTH systems, then respond 200.
 * Webhook authenticity is already verified by Shopify's authenticate.webhook
 * helper in the Remix template before these functions are called.
 */

import { postToVenturex } from "./venturex-sync.server.js";

export async function handleCustomersDataRequest(shop, payload) {
  // Forward the request to VentureX privacy endpoint; it emails an export.
  await postToVenturex("/privacy/customers/data-request", {
    shop_domain: shop,
    customer: payload.customer,
    orders_requested: payload.orders_requested ?? [],
    requested_at: payload.requested_at,
  });
  return new Response();
}

export async function handleCustomersRedact(shop, payload) {
  await postToVenturex("/privacy/customers/redact", {
    shop_domain: shop,
    customer: payload.customer,
    orders_to_redact: payload.orders_to_redact ?? [],
  });
  return new Response();
}

export async function handleShopRedact(shop, payload) {
  // Merchant uninstalled the app: delete ALL synced data for this store.
  await postToVenturex("/privacy/shop/redact", {
    shop_domain: shop,
  });
  return new Response();
}
