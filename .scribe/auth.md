# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer Bearer {your_jwt_token_here}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

To authenticate API requests, you need to include a JWT token in the `Authorization` header.

**How to get your token:**
1. Call the `/api/login` endpoint with your credentials
2. Copy the `token` from the response
3. Include it in subsequent requests as: `Authorization: Bearer {token}`

**Note:** Tokens expire after 24 hours.
