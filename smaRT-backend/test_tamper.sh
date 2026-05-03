#!/bin/bash
# ──────────────────────────────────────────────────────────────
# smaRT — Test Tampered Blockchain Detection
# ──────────────────────────────────────────────────────────────
# This script tampers the first block's nominal in the DB,
# then calls /kas/monitor to verify it detects the tampering.
# ──────────────────────────────────────────────────────────────

BASE="http://localhost:8000/api"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

print_step() {
    echo ""
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}$1${NC}"
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
}

# ── Step 1: Login as BENDAHARA ────────────────────────────────
print_step "STEP 1: Login as BENDAHARA"
BENDAHARA_RESP=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"NIK":"3578010101010002","password":"password123"}')
BENDAHARA_TOKEN=$(echo "$BENDAHARA_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])" 2>/dev/null)
echo -e "${GREEN}TOKEN: ${BENDAHARA_TOKEN:0:40}...${NC}"

# ── Step 2: TAMPER a block in the DB ──────────────────────────
print_step "STEP 2: 🔴 TAMPERING first block — changing nominal to 99999"
php artisan tinker --execute="
\$block = \App\Models\Blockchain::orderBy('created_at','asc')->orderBy('id','asc')->first();
if (!\$block) { echo 'No blocks found! Run test_api.sh first.'.PHP_EOL; exit(1); }
echo 'Block ID:   '.\$block->id.PHP_EOL;
echo 'Original:   nominal='.\$block->nominal.PHP_EOL;
\$block->nominal = 99999;
\$block->saveQuietly();
echo 'Tampered:   nominal='.\$block->nominal.PHP_EOL;
echo 'Hash NOT recomputed — chain is now broken!'.PHP_EOL;
"

# ── Step 3: Monitor — should be INVALID ───────────────────────
print_step "STEP 3: Monitor (expect INVALID ❌)"
curl -s -X GET "$BASE/kas/monitor" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" | python3 -m json.tool

# ── Step 4: Restore the block ─────────────────────────────────
print_step "STEP 4: ♻️  Restoring original nominal (50000)"
php artisan tinker --execute="
\$block = \App\Models\Blockchain::orderBy('created_at','asc')->orderBy('id','asc')->first();
\$block->nominal = 50000;
\$block->saveQuietly();
echo 'Restored:   nominal='.\$block->nominal.PHP_EOL;
"

# ── Step 5: Monitor — should be VALID again ───────────────────
print_step "STEP 5: Monitor (expect VALID ✅)"
curl -s -X GET "$BASE/kas/monitor" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" | python3 -m json.tool

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  TAMPER TEST COMPLETE${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
