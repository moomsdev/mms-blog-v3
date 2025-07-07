# GitHub Workflows cho Theme mooms_dev

Thư mục này chứa các GitHub Actions workflows để tự động kiểm tra chất lượng code và deploy theme mooms_dev.

## 📋 Danh sách Workflows

### 1. 🎯 **theme-lint.yml** - Theme Code Quality Check

**Trigger:** Push hoặc Pull Request vào branch `master` với thay đổi trong theme

-   ✅ Kiểm tra PHP coding standards với PHPCS
-   ✅ Linting JavaScript với ESLint
-   ✅ Linting CSS/SCSS với Stylelint
-   ✅ Test build process với Webpack
-   ✅ Cache dependencies để tăng tốc

### 2. 🔍 **phpcs.yml** - PHP CodeSniffer

**Trigger:** Push hoặc Pull Request vào branch `master` với thay đổi file PHP

-   ✅ Kiểm tra WordPress coding standards chi tiết
-   ✅ Tạo annotations cho lỗi trong Pull Request
-   ✅ Sử dụng cấu hình phpcs.xml của theme

### 3. 🏗️ **build-test.yml** - Build and Test Theme

**Trigger:** Push vào `master`/`develop` hoặc Pull Request vào `master`

-   ✅ Test build với multiple Node.js versions (18, 20)
-   ✅ Test compatibility với multiple PHP versions (7.4, 8.0, 8.1, 8.2)
-   ✅ Kiểm tra security vulnerabilities
-   ✅ Upload build artifacts
-   ✅ Syntax checking

### 4. 🚀 **ci.yml** - FTP Deploy

**Trigger:** Push vào branch `master` với thay đổi trong theme

-   ✅ Tự động deploy theme lên server qua FTP
-   ⚙️ Cần cấu hình secrets: `FTP_SERVER`, `FTP_USERNAME`, `FTP_PASSWORD`

## 🔧 Cấu hình cần thiết

### GitHub Secrets (cho FTP Deploy)

Vào **Settings** > **Secrets and variables** > **Actions** và thêm:

```
FTP_SERVER=your-ftp-server.com
FTP_USERNAME=your-username
FTP_PASSWORD=your-password
```

### Branch Protection Rules (khuyên dùng)

Vào **Settings** > **Branches** và setup rules cho branch `master`:

-   ✅ Require pull request reviews
-   ✅ Require status checks to pass before merging
-   ✅ Require branches to be up to date before merging

## 📁 Cấu trúc Theme

Workflows này được thiết kế cho theme structure:

```
app/public/wp-content/themes/mooms_dev/
├── composer.json          # PHP dependencies
├── package.json           # Node.js dependencies
├── yarn.lock             # Yarn lock file
├── phpcs.xml             # PHP CodeSniffer config
├── .eslintrc.js          # ESLint config
├── .stylelintrc.json     # Stylelint config
├── webpack.config.js     # Webpack config
└── dist/                 # Build output directory
```

## 🔄 Workflow Triggers

| Workflow    | Push to master | PR to master | File changes |
| ----------- | -------------- | ------------ | ------------ |
| theme-lint  | ✅             | ✅           | theme/\*\*   |
| phpcs       | ✅             | ✅           | \*.php       |
| build-test  | ✅             | ✅           | theme/\*\*   |
| ci (deploy) | ✅             | ❌           | theme/\*\*   |

## 📊 Status Badges

Thêm badges vào README.md của theme:

```markdown
[![Theme Quality Check](https://github.com/your-username/repo-name/workflows/Theme%20Code%20Quality%20Check/badge.svg)](https://github.com/your-username/repo-name/actions)
[![Build Test](https://github.com/your-username/repo-name/workflows/Build%20and%20Test%20Theme/badge.svg)](https://github.com/your-username/repo-name/actions)
```

## 🐛 Troubleshooting

### Common Issues:

1. **Build fails:** Kiểm tra `yarn.lock` và `package.json` compatibility
2. **PHPCS errors:** Xem file `phpcs.xml` và coding standards
3. **Deploy fails:** Kiểm tra FTP credentials trong GitHub Secrets
4. **Cache issues:** Manually clear cache trong Actions tab

### Debug Commands (local):

```bash
# Test build locally
cd app/public/wp-content/themes/mooms_dev
yarn install
yarn build

# Test PHP standards
composer install
./vendor/bin/phpcs --standard=phpcs.xml .

# Test linting
npx eslint resources/scripts/**/*.js
npx stylelint "resources/styles/**/*.scss"
```
