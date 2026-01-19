# User Commands

This document covers user management commands available in MODX CLI.

## List Users

```bash
modx user:list --limit=0
```

## Get User Details

```bash
modx user:get 1
modx user:get admin
```

## Create a User

```bash
modx user:create jane --email=jane@example.com --password='StrongPass123!' --fullname='Jane Doe'
```

## Update a User

```bash
modx user:update 1 --email=jane+updated@example.com --fullname='Jane Q. Doe'
```

## Remove a User

```bash
modx user:remove 1 --force
```

## Activate or Deactivate a User

```bash
modx user:activate 1
modx user:deactivate 1
```

## Block or Unblock a User

```bash
modx user:block 1
modx user:unblock 1
```

## Reset a Password

```bash
modx user:resetpassword 1 --password='NewPass123!'
```

## Security Considerations

- Treat generated or provided passwords as sensitive; avoid sharing logs or console output.
- Avoid blocking or deactivating your own admin account without a recovery path.
- Use `--force` on `user:remove` only when you are certain about the target user.
- Prefer `--json` output for automation to avoid parsing human-readable messages.
