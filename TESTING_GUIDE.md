# 🧪 FitSupps Testing Guide

## ✅ All Features Ready to Test

### Latest Updates (Just Applied)
1. ✅ Cart count badge in header
2. ✅ Remove from cart functionality
3. ✅ Update quantity (+/- buttons)
4. ✅ Changed all prices from $ to ₹ (Indian Rupee)

---

## 🚀 Quick Start Testing

### Step 1: Refresh Browser
**CRITICAL:** Press `Ctrl + F5` to clear cache

### Step 2: Login
- Go to: `http://localhost/client/login.php`
- Use your account or register new one

### Step 3: Test Cart Features

#### A. Cart Count Badge
1. Look at top navigation
2. Cart icon should show red badge with number
3. Example: Cart (3) means 3 items

#### B. Add to Cart
1. Go to homepage or products page
2. Click "Add to Cart" button (cart icon)
3. Should see:
   - ✅ Success notification
   - ✅ Cart icon animates
   - ✅ Badge number increases

#### C. View Cart
1. Click "Cart" in navigation
2. Should see all your items
3. Prices shown in ₹ (Rupee)

#### D. Remove from Cart
1. Click "Remove" button
2. Confirm in dialog
3. Item should be removed
4. Page refreshes automatically

#### E. Update Quantity
1. Click + button to increase
2. Click - button to decrease
3. Page refreshes with new quantity
4. Total updates automatically

---

## 🛒 Complete Cart Flow Test

### Test Scenario 1: Add Multiple Products
1. Add 3 different products to cart
2. Check badge shows "3"
3. Go to cart page
4. Verify all 3 products are there
5. Check total is correct (in ₹)

### Test Scenario 2: Update Quantities
1. In cart, click + on first product
2. Wait for page refresh
3. Verify quantity increased
4. Verify total increased
5. Click - on second product
6. Verify quantity decreased

### Test Scenario 3: Remove Items
1. Click Remove on one product
2. Click OK in confirmation
3. Wait for page refresh
4. Verify product is gone
5. Verify total updated
6. Check badge number decreased

### Test Scenario 4: Checkout
1. Add products to cart
2. Click "Proceed to Checkout"
3. Select delivery address
4. Place order
5. Check "My Orders" page

---

## 👨‍💼 Admin Panel Testing

### Login to Admin
- URL: `http://localhost/client/admin/login.php`
- Username: `admin`
- Password: `password`

### Test Product Management

#### Add Product with Images
1. Go to Products → Add New Product
2. Fill in details:
   - Name: "Whey Protein Gold"
   - Category: Whey Protein
   - Price: 2999
   - Stock: 50
   - Weight: "1kg"
   - Status: Active
3. Upload images (1-5):
   - Option 1: Upload JPG/PNG files
   - Option 2: Paste image URLs
4. Click "Add Product"
5. Check if product appears in list

#### View Products
1. Go to Products page
2. Should see all products
3. Prices in ₹
4. Edit/Delete buttons work

#### Manage Orders
1. Go to Orders page
2. View order details
3. Update order status
4. Check amounts in ₹

---

## 🐛 Troubleshooting

### Cart Badge Not Showing?
**Solution:**
1. Make sure you're logged in
2. Add at least one product
3. Refresh with `Ctrl + F5`

### Add to Cart Not Working?
**Check:**
1. Are you logged in?
2. Does product have stock?
3. Open browser console (F12)
4. Look for red errors

### Remove Not Working?
**Check:**
1. Did you click OK on confirmation?
2. Check browser console (F12)
3. Make sure you're logged in

### Products Not Adding in Admin?
**Solution:**
Run this SQL in phpMyAdmin:
```sql
ALTER TABLE products ADD COLUMN weight VARCHAR(50) AFTER stock;
```

### Prices Showing as $0.00?
**Solution:**
1. Refresh with `Ctrl + F5`
2. All prices should now show ₹

---

## 📊 What to Check

### Frontend (User Side)
- [ ] Homepage loads
- [ ] Products display with ₹ prices
- [ ] Login/Register works
- [ ] Add to cart works
- [ ] Cart badge shows count
- [ ] Cart page shows items
- [ ] Remove from cart works
- [ ] Update quantity works
- [ ] Checkout works
- [ ] Orders page shows orders

### Admin Panel
- [ ] Admin login works
- [ ] Dashboard shows stats
- [ ] Can add products
- [ ] Can upload images
- [ ] Products list shows
- [ ] Can edit products
- [ ] Orders list shows
- [ ] Can view order details
- [ ] Can update order status

---

## 💡 Important Notes

### Currency
- All prices now show ₹ (Indian Rupee)
- Changed from $ to ₹ across entire site

### Cart Features
- Badge shows total quantity (not unique items)
- Example: 2 of Product A + 3 of Product B = Badge shows "5"
- Maximum badge shows "9+" if more than 9 items

### Product Images
- Can upload 1-5 images per product
- Supports: JPG, PNG, GIF, WebP
- Max size: 5MB per image
- Can use file upload OR image URL

### Weight Field
- Required for all products
- Examples: "1kg", "500g", "2.5kg", "250g"
- Shows on product cards with weight icon

---

## 🎯 Success Criteria

Your website is working correctly if:

✅ Cart badge shows item count
✅ Add to cart shows notification
✅ Remove from cart works with confirmation
✅ Quantity update works (+/- buttons)
✅ All prices show ₹ symbol
✅ Admin can add products with images
✅ Products show on homepage
✅ Checkout process works
✅ Orders are tracked

---

## 📞 Need Help?

If something doesn't work:

1. **Check browser console** (F12)
   - Look for red errors
   - Copy error messages

2. **Use debug tools**
   - `admin/check-products.php` - Check products
   - `admin/simple-product-test.php` - Test product add
   - `test-cart.php` - Test cart API

3. **Common fixes**
   - Refresh with `Ctrl + F5`
   - Clear browser cache
   - Check if logged in
   - Run SQL commands from docs

---

## 📁 Documentation Files

- `README.md` - Main documentation
- `QUICK_START.md` - Quick start guide
- `INSTALLATION.md` - Setup instructions
- `SETUP_HINDI.md` - Hindi setup guide
- `CURRENT_STATUS.md` - Current status
- `QUICK_FIX.md` - Latest fixes
- `TESTING_GUIDE.md` - This file

---

**Ready to test!** Start with Step 1 above. 🚀
