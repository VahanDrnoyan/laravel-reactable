#!/bin/bash

# Laravel Reactable v1.0.0 - Release Commands
# Run these commands to publish the package

echo "🚀 Laravel Reactable v1.0.0 Release Script"
echo "=========================================="
echo ""

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: composer.json not found. Are you in the package directory?"
    exit 1
fi

echo "📋 Step 1: Verify everything is committed"
if [ -n "$(git status --porcelain)" ]; then
    echo "⚠️  Warning: You have uncommitted changes"
    git status --short
    read -p "Do you want to continue? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
else
    echo "✅ Working directory is clean"
fi

echo ""
echo "📋 Step 2: Run tests"
composer test
if [ $? -ne 0 ]; then
    echo "❌ Tests failed! Fix them before releasing."
    exit 1
fi
echo "✅ All tests passed"

echo ""
echo "📋 Step 3: Push to GitHub"
read -p "Push to GitHub? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    git push origin main
    echo "✅ Pushed to main branch"
    
    git push origin v1.0.0
    echo "✅ Pushed v1.0.0 tag"
fi

echo ""
echo "📋 Step 4: Next Steps"
echo ""
echo "✅ Tag v1.0.0 created and pushed"
echo ""
echo "🎯 Manual steps required:"
echo ""
echo "1. Create GitHub Release:"
echo "   → Visit: https://github.com/vahandr/laravel-reactable/releases/new"
echo "   → Select tag: v1.0.0"
echo "   → Title: v1.0.0 - Initial Stable Release"
echo "   → Copy description from CHANGELOG.md"
echo "   → Publish release"
echo ""
echo "2. Submit to Packagist:"
echo "   → Visit: https://packagist.org/packages/submit"
echo "   → Enter: https://github.com/vahandr/laravel-reactable"
echo "   → Click Submit"
echo ""
echo "3. Set up Auto-Update:"
echo "   → Copy webhook URL from Packagist"
echo "   → Add to GitHub Settings → Webhooks"
echo ""
echo "4. Verify Installation:"
echo "   → composer require truefans/laravel-reactable"
echo ""
echo "📖 See PUBLISHING_GUIDE.md for detailed instructions"
echo ""
echo "🎉 Release preparation complete!"
