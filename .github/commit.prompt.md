# 🚀 Git Commit & Branch Conventions

## 📝 Commit Message Format
```
type(scope): concise description

[optional body]

[optional footer]
```

## 💎 Commit Types
| Type | Description | Example |
|------|-------------|---------|
| `feat` | - New features or significant additions | `feat(products): add medication search functionality` |
| `fix` | - Bug fixes | `fix(checkout): resolve payment processing error` |
| `docs` | - Documentation changes | `docs(readme): update installation instructions` |
| `style` | - Code style/formatting (no functional changes) | `style(components): apply consistent indentation` |
| `refactor` | - Code refactoring without changing functionality | `refactor(cart): simplify discount calculation logic` |
| `test` | - Adding or modifying tests | `test(auth): add unit tests for login flow` |
| `chore` | - Maintenance tasks, dependencies, config | `chore(deps): update React to v18.2.0` |
| `perf` | - Performance improvements | `perf(images): optimize product image loading` |
| `ci` | - CI/CD pipeline changes | `ci(workflow): add automated testing step` |

## 🌿 Branch Naming
- Feature branches: `feature/feature-name`
- Bug fixes: `bugfix/issue-description`
- Hotfixes: `hotfix/critical-issue`
- Releases: `release/v1.2.3`
- Documentation: `docs/update-area`

## ✨ Best Practices
- Keep commits **atomic** and focused on single changes
- Write in **imperative mood** ("add feature" not "added feature")
- Reference issues when applicable: `fix(auth): resolve login timeout (#123)`
- First line should be under 72 characters
- Use body for explaining "why" not "how"

## 🔍 Examples
```
feat(consultation): implement AI chatbot interface

- Add LLaMA 3.3 integration for handling basic customer inquiries about medications and health advice.
- Includes conversation state management and pharmacy handoff when needed.

Resolves #45
```