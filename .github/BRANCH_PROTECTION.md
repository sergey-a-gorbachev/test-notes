# Защита ветки `main`

GitHub branch protection настраивается в репозитории: **Settings → Branches → Add branch protection rule**.

Для `main` необходимо включить:

- Require a pull request before merging;
- Require approvals: 1;
- Dismiss stale pull request approvals when new commits are pushed;
- Require status checks to pass before merging;
- Require branches to be up to date before merging;
- Do not allow bypassing the above settings.

Обязательные проверки workflow `CI`:

- `backend`;
- `frontend`;
- `smoke`.

Эти настройки хранятся на стороне GitHub и не применяются файлом из репозитория. Для автоматической настройки нужен
токен с административным доступом к репозиторию.
