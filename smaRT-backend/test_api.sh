#!/bin/bash
# ──────────────────────────────────────────────────────────────
# smaRT API Full Test Script
# ──────────────────────────────────────────────────────────────

BASE="http://localhost:8000/api"
RT_ID="019dd189-5042-7141-acf0-de0571dc59f6"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

print_test() {
    echo ""
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
    echo -e "${YELLOW}TEST: $1${NC}"
    echo -e "${CYAN}════════════════════════════════════════════════════════════${NC}"
}

# ──────────────────────────────────────────────────────────────
# 1. LOGIN — KETUA
# ──────────────────────────────────────────────────────────────
print_test "1. POST /auth/login (KETUA)"
KETUA_RESP=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"NIK":"3578010101010001","password":"password123"}')
echo "$KETUA_RESP" | python3 -m json.tool 2>/dev/null || echo "$KETUA_RESP"
KETUA_TOKEN=$(echo "$KETUA_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])" 2>/dev/null)
echo -e "${GREEN}KETUA TOKEN: ${KETUA_TOKEN:0:40}...${NC}"

# ──────────────────────────────────────────────────────────────
# 2. LOGIN — BENDAHARA
# ──────────────────────────────────────────────────────────────
print_test "2. POST /auth/login (BENDAHARA)"
BENDAHARA_RESP=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"NIK":"3578010101010002","password":"password123"}')
echo "$BENDAHARA_RESP" | python3 -m json.tool 2>/dev/null || echo "$BENDAHARA_RESP"
BENDAHARA_TOKEN=$(echo "$BENDAHARA_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])" 2>/dev/null)
echo -e "${GREEN}BENDAHARA TOKEN: ${BENDAHARA_TOKEN:0:40}...${NC}"

# ──────────────────────────────────────────────────────────────
# 3. LOGIN — WARGA
# ──────────────────────────────────────────────────────────────
print_test "3. POST /auth/login (WARGA)"
WARGA_RESP=$(curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"NIK":"3578010101010003","password":"password123"}')
echo "$WARGA_RESP" | python3 -m json.tool 2>/dev/null || echo "$WARGA_RESP"
WARGA_TOKEN=$(echo "$WARGA_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])" 2>/dev/null)
echo -e "${GREEN}WARGA TOKEN: ${WARGA_TOKEN:0:40}...${NC}"

# ──────────────────────────────────────────────────────────────
# 4. LOGIN — Wrong password (should fail 401)
# ──────────────────────────────────────────────────────────────
print_test "4. POST /auth/login (WRONG PASSWORD — expect 401)"
curl -s -X POST "$BASE/auth/login" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"NIK":"3578010101010001","password":"wrongpass"}' | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 5. REGISTER — KETUA registers a new WARGA
# ──────────────────────────────────────────────────────────────
print_test "5. POST /auth/register (KETUA registers new WARGA — expect 201)"
curl -s -X POST "$BASE/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $KETUA_TOKEN" \
  -d "{\"id_rt\":\"$RT_ID\",\"nama\":\"Dewi Warga Baru\",\"NIK\":\"3578010101010099\",\"role\":\"WARGA\",\"phone\":\"081999999999\",\"password\":\"password123\"}" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 6. REGISTER — WARGA tries to register (should fail 403)
# ──────────────────────────────────────────────────────────────
print_test "6. POST /auth/register (WARGA tries to register — expect 403)"
curl -s -X POST "$BASE/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" \
  -d "{\"id_rt\":\"$RT_ID\",\"nama\":\"Hacker\",\"NIK\":\"9999999999999999\",\"role\":\"WARGA\",\"password\":\"password123\"}" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 7. REGISTER — Duplicate NIK (should fail 409/422)
# ──────────────────────────────────────────────────────────────
print_test "7. POST /auth/register (Duplicate NIK — expect 422)"
curl -s -X POST "$BASE/auth/register" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $KETUA_TOKEN" \
  -d "{\"id_rt\":\"$RT_ID\",\"nama\":\"Duplicate\",\"NIK\":\"3578010101010001\",\"role\":\"WARGA\",\"password\":\"password123\"}" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 8. SURAT AJUKAN — WARGA submits a letter
# ──────────────────────────────────────────────────────────────
print_test "8. POST /surat/ajukan (WARGA submits letter — expect 201)"
SURAT_RESP=$(curl -s -X POST "$BASE/surat/ajukan" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" \
  -d '{"nama_surat":"Surat Pengantar KTP","deskripsi_surat":"Pengajuan surat pengantar untuk pembuatan KTP baru.","dokumen_pendukung":"scan_kk.pdf"}')
echo "$SURAT_RESP" | python3 -m json.tool 2>/dev/null || echo "$SURAT_RESP"
SURAT_ID=$(echo "$SURAT_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['id'])" 2>/dev/null)
echo -e "${GREEN}SURAT ID: $SURAT_ID${NC}"

# ──────────────────────────────────────────────────────────────
# 9. SURAT REVIEW — KETUA approves the letter
# ──────────────────────────────────────────────────────────────
print_test "9. PATCH /surat/ajukan (KETUA approves letter — expect 200)"
curl -s -X PATCH "$BASE/surat/ajukan" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $KETUA_TOKEN" \
  -d "{\"id\":\"$SURAT_ID\",\"status\":\"APPROVED\",\"file_final\":\"surat_signed.pdf\"}" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 10. PANIC TRIGGER — WARGA triggers panic button
# ──────────────────────────────────────────────────────────────
print_test "10. POST /panic/trigger (WARGA triggers panic — expect 200)"
curl -s -X POST "$BASE/panic/trigger" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" \
  -d '{"latitude":"-7.2945","longitude":"112.7681"}' | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 11. KAS INPUT — BENDAHARA records first transaction
# ──────────────────────────────────────────────────────────────
print_test "11. POST /kas/input (BENDAHARA — Transaction 1 — expect 201)"
curl -s -X POST "$BASE/kas/input" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -d '{"jenis_kas":"PEMASUKAN","nominal":50000,"keterangan":"Iuran Kas April - Pak Budi"}' | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 12. KAS INPUT — BENDAHARA records second transaction (chain)
# ──────────────────────────────────────────────────────────────
print_test "12. POST /kas/input (BENDAHARA — Transaction 2 — expect 201)"
curl -s -X POST "$BASE/kas/input" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $BENDAHARA_TOKEN" \
  -d '{"jenis_kas":"PENGELUARAN","nominal":25000,"keterangan":"Beli air galon untuk balai RT"}' | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 13. KAS HISTORY — Get all transaction blocks
# ──────────────────────────────────────────────────────────────
print_test "13. GET /kas/history (All users — expect 200)"
curl -s -X GET "$BASE/kas/history" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 14. KAS MONITOR — Verify hashchain integrity
# ──────────────────────────────────────────────────────────────
print_test "14. GET /kas/monitor (Integrity check — expect 200)"
curl -s -X GET "$BASE/kas/monitor" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 15. BROADCAST — KETUA sends announcement
# ──────────────────────────────────────────────────────────────
print_test "15. POST /broadcast (KETUA sends announcement — expect 201)"
curl -s -X POST "$BASE/broadcast" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $KETUA_TOKEN" \
  -d '{"judul":"Kerja Bakti Minggu Legi","isi_pesan":"Diharapkan seluruh warga berkumpul di balai RT pukul 07.00 WIB.","kategori":"KEGIATAN"}' | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 16. BROADCAST — Get list
# ──────────────────────────────────────────────────────────────
print_test "16. GET /broadcast (List announcements — expect 200)"
curl -s -X GET "$BASE/broadcast?limit=10" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 17. REFRESH TOKEN
# ──────────────────────────────────────────────────────────────
print_test "17. POST /auth/refresh (Refresh WARGA token — expect 200)"
REFRESH_RESP=$(curl -s -X POST "$BASE/auth/refresh" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $WARGA_TOKEN")
echo "$REFRESH_RESP" | python3 -m json.tool
NEW_WARGA_TOKEN=$(echo "$REFRESH_RESP" | python3 -c "import sys,json; print(json.load(sys.stdin)['token'])" 2>/dev/null)
echo -e "${GREEN}NEW WARGA TOKEN: ${NEW_WARGA_TOKEN:0:40}...${NC}"

# ──────────────────────────────────────────────────────────────
# 18. LOGOUT
# ──────────────────────────────────────────────────────────────
print_test "18. POST /auth/logout (Logout WARGA — expect 200)"
curl -s -X POST "$BASE/auth/logout" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $NEW_WARGA_TOKEN" | python3 -m json.tool

# ──────────────────────────────────────────────────────────────
# 19. ACCESS AFTER LOGOUT (should fail 401)
# ──────────────────────────────────────────────────────────────
print_test "19. GET /broadcast (After logout — expect 401)"
curl -s -X GET "$BASE/broadcast" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $NEW_WARGA_TOKEN" | python3 -m json.tool

echo ""
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  ALL 19 TESTS COMPLETED${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════════${NC}"
