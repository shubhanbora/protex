# Mobile Images Guide - FitSupps

## 📱 Mobile Version के लिए Images कहां रखें

### **Folder Structure:**
```
assets/
├── images/
    ├── slider/
    │   ├── slide1.jpg          (Desktop images)
    │   ├── slide2.jpg
    │   ├── slide3.jpg
    │   ├── slide4.jpg
    │   └── mobile/
    │       ├── slide1.jpg      (Mobile images)
    │       ├── slide2.jpg
    │       ├── slide3.jpg
    │       └── slide4.jpg
```

### **Mobile Images की Requirements:**

#### **Size Recommendations:**
- **Desktop**: 1920x1080 या 1600x900 (Landscape)
- **Mobile**: 768x1024 या 375x667 (Portrait या Square)

#### **File Names (Exact):**
- `slide1.jpg` - First slider image
- `slide2.jpg` - Second slider image  
- `slide3.jpg` - Third slider image
- `slide4.jpg` - Fourth slider image

### **कैसे काम करता है:**

1. **Desktop पर**: `assets/images/slider/` से images load होती हैं
2. **Mobile पर**: `assets/images/slider/mobile/` से images load होती हैं
3. **Auto-detection**: JavaScript automatically detect करता है कि device mobile है या desktop

### **Images Upload करने के Steps:**

#### **Step 1: Mobile Images तैयार करें**
- Mobile के लिए vertical या square format बेहतर है
- File size कम रखें (mobile के लिए)
- Clear और readable images use करें

#### **Step 2: Upload Location**
```
📁 assets/images/slider/mobile/
   ├── slide1.jpg  ← यहां अपनी mobile image रखें
   ├── slide2.jpg  ← यहां अपनी mobile image रखें
   ├── slide3.jpg  ← यहां अपनी mobile image रखें
   └── slide4.jpg  ← यहां अपनी mobile image रखें
```

#### **Step 3: Test करें**
1. Desktop पर website खोलें - desktop images दिखनी चाहिए
2. Mobile पर या browser को mobile size करें - mobile images दिखनी चाहिए

### **Current Status:**
✅ Mobile folder बन गया है  
✅ Temporary mobile images copy हो गई हैं  
✅ Responsive JavaScript add हो गया है  
⏳ आपको अपनी actual mobile images upload करनी हैं  

### **Features:**
- **Auto-switching**: Screen size के हिसाब से images change होती हैं
- **Touch support**: Mobile पर swipe करके slide change कर सकते हैं
- **Performance**: Mobile images अलग से optimize कर सकते हैं
- **Responsive**: Window resize पर automatically switch होता है

### **Tips:**
1. Mobile images को compress करें fast loading के लिए
2. Text वाली images avoid करें mobile पर
3. High contrast images use करें better visibility के लिए
4. Same aspect ratio maintain करें smooth transitions के लिए

अब आप `assets/images/slider/mobile/` folder में अपनी mobile-specific images रख सकते हैं!