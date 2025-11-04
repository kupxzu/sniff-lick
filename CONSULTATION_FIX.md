# 🚨 CONSULTATION FIX - How to Create Treatments

## ❌ **WRONG WAY (Your Error):**
```json
POST /api/treatments
{
    "treatment_type": "medicine",
    "meds_name": "asd", 
    "treatment_details": "asd"
}
```
**Error:** `"consultation_id field is required"`

## ✅ **CORRECT WAY - Use Hierarchical Route:**

### **Step 1: Get the IDs you need**
You need:
- `client_id` (from client list)
- `pet_id` (from pet list) 
- `consultation_id` (from consultation list)

### **Step 2: Use the hierarchical URL**
```json
POST /api/admin/clients/{client_id}/pets/{pet_id}/consultations/{consultation_id}/treatments

Headers:
Authorization: Bearer {ADMIN_TOKEN}
Content-Type: application/json

Body:
{
    "treatment_type": "medicine",
    "meds_name": "Rimadyl 100mg",
    "treatment_details": "Anti-inflammatory medication - 1 tablet twice daily for 7 days"
}
```

## 📋 **COMPLETE EXAMPLE:**

### **1. Login as Admin**
```bash
POST http://127.0.0.1:8000/api/auth/login
{
    "login": "admin",
    "password": "password123" 
}
```

### **2. Get Clients**
```bash
GET http://127.0.0.1:8000/api/admin/clients
Authorization: Bearer {TOKEN}
```

### **3. Get Client's Pets** 
```bash
GET http://127.0.0.1:8000/api/admin/clients/1/pets
Authorization: Bearer {TOKEN}
```

### **4. Get Pet's Consultations**
```bash
GET http://127.0.0.1:8000/api/admin/clients/1/pets/1/consultations  
Authorization: Bearer {TOKEN}
```

### **5. Create Treatment (FIXED)**
```bash
POST http://127.0.0.1:8000/api/admin/clients/1/pets/1/consultations/1/treatments
Authorization: Bearer {TOKEN}
Content-Type: application/json

{
    "treatment_type": "medicine",
    "meds_name": "Rimadyl 100mg", 
    "treatment_details": "1 tablet twice daily with food"
}
```

## 🎯 **KEY POINTS:**

1. **NO consultation_id in body** - it comes from URL
2. **Use admin token** for hierarchical routes
3. **Replace numbers** with actual IDs from your data
4. **Follow the hierarchy**: client → pet → consultation → treatment

## 📝 **Valid Treatment Types:**
- `"medicine"` (requires meds_name)
- `"surgery"` 
- `"confinement"`

## 🧪 **Lab Test Example:**
```bash
POST http://127.0.0.1:8000/api/admin/clients/1/pets/1/consultations/1/labtests

{
    "lab_types": "cbc",
    "photo_result": ["blood_test.jpg"],
    "notes": "Normal range"
}
```

## 📋 **Prescription Example:**
```bash
POST http://127.0.0.1:8000/api/admin/clients/1/pets/1/consultations/1/prescriptions

{
    "upload_photo": ["prescription.jpg"],
    "description": "Take 1 tablet twice daily"
}
```

---
**The consultation_id is automatically extracted from the URL path!** 🎉