#!/bin/bash

# Script to find hardcoded text that should be translatable
# Run from plugin directory

echo "🔍 Finding Hardcoded Text in SIT Connect Plugin"
echo "================================================"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter
total_issues=0

echo "1️⃣  Checking for echo with hardcoded strings..."
echo "------------------------------------------------"
count=$(grep -r "echo ['\"]" src/ templates/ --include="*.php" | grep -v "__(" | grep -v "_e(" | grep -v "esc_html" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${RED}Found $count instances${NC}"
    grep -rn "echo ['\"]" src/ templates/ --include="*.php" | grep -v "__(" | grep -v "_e(" | grep -v "esc_html" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "2️⃣  Checking for hardcoded array values..."
echo "------------------------------------------------"
count=$(grep -r "=> ['\"][A-Za-z]" src/ templates/ --include="*.php" | grep -v "__(" | grep -v "http" | grep -v "sit-" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${YELLOW}Found $count instances (review manually)${NC}"
    grep -rn "=> ['\"][A-Za-z]" src/ templates/ --include="*.php" | grep -v "__(" | grep -v "http" | grep -v "sit-" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "3️⃣  Checking for hardcoded HTML text..."
echo "------------------------------------------------"
count=$(grep -r "<h[1-6]>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${RED}Found $count instances${NC}"
    grep -rn "<h[1-6]>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "4️⃣  Checking for hardcoded button text..."
echo "------------------------------------------------"
count=$(grep -r "<button[^>]*>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${RED}Found $count instances${NC}"
    grep -rn "<button[^>]*>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "5️⃣  Checking for hardcoded placeholder text..."
echo "------------------------------------------------"
count=$(grep -r "placeholder=['\"][A-Za-z]" templates/ src/ --include="*.php" | grep -v "esc_attr__" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${RED}Found $count instances${NC}"
    grep -rn "placeholder=['\"][A-Za-z]" templates/ src/ --include="*.php" | grep -v "esc_attr__" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "6️⃣  Checking for hardcoded label text..."
echo "------------------------------------------------"
count=$(grep -r "<label[^>]*>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | wc -l)
if [ $count -gt 0 ]; then
    echo -e "${RED}Found $count instances${NC}"
    grep -rn "<label[^>]*>[A-Za-z]" templates/ --include="*.php" | grep -v "<?php" | head -10
    echo "..."
    total_issues=$((total_issues + count))
else
    echo -e "${GREEN}✓ No issues found${NC}"
fi
echo ""

echo "================================================"
echo "📊 Summary"
echo "================================================"
echo -e "Total potential issues: ${YELLOW}$total_issues${NC}"
echo ""
echo "💡 Next Steps:"
echo "1. Review each instance manually"
echo "2. Replace with translation functions:"
echo "   - Use __() to return translated string"
echo "   - Use _e() to echo translated string"
echo "   - Use esc_html__() for escaped output"
echo "   - Use esc_attr__() for HTML attributes"
echo "3. Always use text domain: 'sit-connect'"
echo ""
echo "📚 See TRANSLATABLE_TEXT_GUIDE.md for detailed examples"
echo ""
