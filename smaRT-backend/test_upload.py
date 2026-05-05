import requests
import json

BASE_URL = "http://localhost:8000/api"

# 1. Login as WARGA
print("Logging in as WARGA...")
resp = requests.post(f"{BASE_URL}/auth/login", json={
    "NIK": "3578010101010003",
    "password": "password123"
})

if resp.status_code != 200:
    print("Login failed!", resp.text)
    exit(1)

token = resp.json()["token"]
print("Login successful, obtained token.")

# 2. Create a dummy image
img_path = "/tmp/dummy_attachment.png"
# Create a 1x1 transparent PNG
with open(img_path, "wb") as f:
    f.write(bytes.fromhex('89504e470d0a1a0a0000000d49484452000000010000000108060000001f15c4890000000a49444154789c63000100000500010d0a2db40000000049454e44ae426082'))

# 3. Submit Pengajuan Surat
print("Submitting Surat Pengajuan...")
headers = {
    "Authorization": f"Bearer {token}",
    "Accept": "application/json"
}

data = {
    "nama_surat": "Surat Keterangan Usaha (Test Upload)",
    "deskripsi_surat": "Pengajuan surat keterangan untuk keperluan usaha, beserta lampiran KTP."
}

files = {
    "dokumen_pendukung": ("lampiran_ktp.png", open(img_path, "rb"), "image/png")
}

resp = requests.post(f"{BASE_URL}/surat/ajukan", headers=headers, data=data, files=files)

print("Status Code:", resp.status_code)
try:
    print("Response JSON:")
    print(json.dumps(resp.json(), indent=2))
except Exception:
    print("Response Text:", resp.text)
