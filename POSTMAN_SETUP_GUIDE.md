# 🐾 Veterinary API - Postman Testing Guide

## 📥 **SETUP INSTRUCTIONS**

### **1. Import Collection into Postman**
1. Open Postman
2. Click "Import" button
3. Select `Veterinary_API_Postman_Collection.json`
4. Collection will be imported with all requests

### **2. Environment Setup**
The collection uses variables that are automatically set during testing:
- `BASE_URL`: `http://127.0.0.1:8000` (default Laravel dev server)
- `ADMIN_TOKEN`: Auto-set after admin login
- `CLIENT_TOKEN`: Auto-set after client login
- `CLIENT_ID`, `PET_ID`, `CONSULTATION_ID`: Auto-set during workflow

## 🚀 **TESTING WORKFLOW**

### **Step 1: Start Laravel Server**
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### **Step 2: Follow This Order in Postman**

#### **🔐 Authentication (Required First)**
1. **Register Admin** - Creates admin user
2. **Register Client** - Creates client user  
3. **Login Admin** - Gets admin token (saves to `ADMIN_TOKEN`)
4. **Login Client** - Gets client token (saves to `CLIENT_TOKEN`)

#### **👥 Admin Operations (Use Admin Token)**
5. **Get All Clients** - Lists all clients (saves first `CLIENT_ID`)
6. **Get Specific Client** - Shows client details

#### **🐕 Pet Management**
7. **Create Pet for Client** - Creates pet (saves `PET_ID`)
8. **Get Client's Pets** - Lists client's pets
9. **Get Specific Pet** - Shows pet details

#### **🏥 Consultation Management**
10. **Create Consultation** - Creates consultation (saves `CONSULTATION_ID`)
11. **Get Pet's Consultations** - Lists pet's consultations
12. **Get Specific Consultation** - Shows consultation details

#### **🧪 Medical Records**
13. **Create Lab Test** - Adds CBC test
14. **Create X-Ray Test** - Adds X-ray test
15. **Create Medicine Treatment** - Adds medication
16. **Create Confinement Treatment** - Adds rest treatment
17. **Create Prescription** - Adds prescription

#### **📊 View Results**
18. **Get Latest Records** - Shows recent medical data
19. **Admin Dashboard** - Overview of clinic data

## 🎯 **KEY FEATURES TESTED**

### **✅ Hierarchical URL Structure**
- `/api/admin/clients/{client}/pets/{pet}/consultations/{consultation}/labtests`
- Proper admin-centric navigation

### **✅ Auto-Variable Setting**
- IDs automatically captured and used in subsequent requests
- No manual ID copying needed

### **✅ Medical Record Types**
- **Lab Tests**: CBC, X-ray, Ultrasound, Microscopy, Blood Chemistry
- **Treatments**: Medicine, Surgery, Confinement
- **Prescriptions**: Photo uploads and descriptions

### **✅ Role-Based Access**
- Admin can access all hierarchical routes
- Client access properly restricted

## 🔍 **EXPECTED RESPONSES**

### **Success Responses (200/201)**
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": { ... }
}
```

### **Error Responses (4xx/5xx)**
```json
{
  "success": false,
  "message": "Error description",
  "errors": { ... }
}
```

## 🐛 **TROUBLESHOOTING**

### **Common Issues:**

1. **500 Server Error**
   - Check Laravel server is running
   - Verify database migrations: `php artisan migrate:status`

2. **403 Forbidden**
   - Ensure using admin token for admin routes
   - Check role assignment during registration

3. **404 Not Found**
   - Verify route exists: `php artisan route:list --path=api`
   - Check parameter order matches URL

4. **422 Validation Error**
   - Check required fields in request body
   - Verify enum values (species: canine/feline, lab_types: cbc/xray/etc.)

## 📋 **VALIDATION RULES**

### **Pet Creation:**
- `species`: Must be "canine" or "feline"
- `age`: Integer 0-50
- All fields required except colormark

### **Lab Tests:**
- `lab_types`: Must be "cbc", "microscopy", "bloodchem", "ultrasound", or "xray"
- `photo_result`: Array of image filenames

### **Treatments:**
- `treatment_type`: Must be "medicine", "surgery", or "confinement"
- `meds_name`: Required only for medicine type

## 🎉 **SUCCESS INDICATORS**

✅ All requests return 200/201 status codes  
✅ Variables auto-populate between requests  
✅ Medical records link to consultations properly  
✅ Hierarchical URLs work as expected  
✅ Admin can see all data, clients restricted to own pets  

---
**Ready to test your veterinary consultation system!** 🏥🐾