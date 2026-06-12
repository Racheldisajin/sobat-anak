-- Fix password admin agar sesuai bcrypt Laravel
-- Email admin: felixzanqueen@gmail.com
-- Password setelah update: 12345678

UPDATE users
SET password = '$2y$12$YuMvtNISxaeXJVIGKYo7OuPRoDenZEuyDbjRk2pFClSXXbrrQQdpa',
    role = 'admin',
    updated_at = NOW()
WHERE email = 'felixzanqueen@gmail.com';
