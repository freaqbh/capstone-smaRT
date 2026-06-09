export async function onRequest(context) {
  const { request } = context;
  const url = new URL(request.url);

  // The VPS backend URL
  const TARGET_SERVER = "http://203.194.114.62";

  // Reconstruct the URL pointing to the VPS
  // e.g., https://my-app.pages.dev/api/login -> http://203.194.114.62/api/login
  const targetUrl = new URL(url.pathname + url.search, TARGET_SERVER);

  // Clone the request and modify the target URL
  const proxyRequest = new Request(targetUrl, request);

  // Fetch the data from the backend and return it to the frontend
  // Cloudflare Workers allow outgoing HTTP requests without mixed content errors!
  const response = await fetch(proxyRequest);

  return response;
}
