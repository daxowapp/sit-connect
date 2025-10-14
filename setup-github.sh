#!/bin/bash

# SIT Connect - GitHub Setup Script
# This script will set up your plugin for GitHub auto-updates

echo "🚀 Setting up SIT Connect for GitHub Auto-Updates..."
echo ""

# Navigate to plugin directory
cd "$(dirname "$0")"

# Step 1: Install dependencies
echo "📦 Step 1: Installing Plugin Update Checker library..."
if command -v composer &> /dev/null; then
    composer install
    echo "✅ Dependencies installed!"
else
    echo "❌ Composer not found. Please install composer first:"
    echo "   brew install composer"
    exit 1
fi
echo ""

# Step 2: Initialize git (if not already)
if [ ! -d .git ]; then
    echo "📝 Step 2: Initializing Git repository..."
    git init
    git branch -M main
    echo "✅ Git initialized!"
else
    echo "✅ Step 2: Git already initialized!"
fi
echo ""

# Step 3: Add remote
echo "🔗 Step 3: Adding GitHub remote..."
if git remote | grep -q origin; then
    echo "⚠️  Remote 'origin' already exists. Updating..."
    git remote set-url origin https://github.com/daxowapp/sit-connect.git
else
    git remote add origin https://github.com/daxowapp/sit-connect.git
fi
echo "✅ Remote added: https://github.com/daxowapp/sit-connect.git"
echo ""

# Step 4: Create .gitignore if it doesn't exist
if [ ! -f .gitignore ]; then
    echo "📄 Step 4: Creating .gitignore..."
    echo "✅ .gitignore already created!"
else
    echo "✅ Step 4: .gitignore already exists!"
fi
echo ""

# Step 5: Stage all files
echo "📋 Step 5: Staging files for commit..."
git add .
echo "✅ Files staged!"
echo ""

# Step 6: Create initial commit
echo "💾 Step 6: Creating initial commit..."
if git log &> /dev/null; then
    echo "⚠️  Repository already has commits. Skipping initial commit."
else
    git commit -m "Initial commit: SIT Connect v2.0.0 with GitHub auto-updates"
    echo "✅ Initial commit created!"
fi
echo ""

# Step 7: Push to GitHub
echo "🚀 Step 7: Pushing to GitHub..."
echo "⚠️  You may need to authenticate with GitHub..."
git push -u origin main
if [ $? -eq 0 ]; then
    echo "✅ Code pushed to GitHub!"
else
    echo "❌ Push failed. You may need to:"
    echo "   1. Create the repository on GitHub first"
    echo "   2. Authenticate with GitHub (gh auth login)"
    echo "   3. Or push manually: git push -u origin main"
fi
echo ""

# Step 8: Create first tag
echo "🏷️  Step 8: Creating version tag v2.0.0..."
if git tag | grep -q v2.0.0; then
    echo "⚠️  Tag v2.0.0 already exists. Skipping."
else
    git tag -a v2.0.0 -m "Version 2.0.0 - Initial release with GitHub auto-updates"
    git push origin v2.0.0
    if [ $? -eq 0 ]; then
        echo "✅ Tag v2.0.0 created and pushed!"
    else
        echo "⚠️  Tag created locally. Push manually: git push origin v2.0.0"
    fi
fi
echo ""

# Final instructions
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Setup Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Next Steps:"
echo ""
echo "1. Create GitHub Release:"
echo "   - Go to: https://github.com/daxowapp/sit-connect/releases"
echo "   - Click 'Create a new release'"
echo "   - Select tag: v2.0.0"
echo "   - Title: v2.0.0"
echo "   - Description: Initial release with GitHub auto-updates"
echo "   - Click 'Publish release'"
echo ""
echo "2. For Private Repository (Optional):"
echo "   - Generate GitHub token: https://github.com/settings/tokens"
echo "   - Add to wp-config.php:"
echo "     define('SIT_CONNECT_GITHUB_TOKEN', 'ghp_your_token_here');"
echo ""
echo "3. Test Updates:"
echo "   - Go to WordPress admin → Plugins"
echo "   - Check for updates (may take up to 12 hours)"
echo "   - Or clear cache: wp transient delete --all"
echo ""
echo "🎉 Your plugin now has automatic GitHub updates!"
echo ""
echo "📚 Documentation:"
echo "   - Quick Start: QUICK_START_GITHUB_UPDATES.md"
echo "   - Full Guide: RELEASE_WORKFLOW.md"
echo "   - Setup Guide: GITHUB_AUTO_UPDATE_SETUP.md"
echo ""
