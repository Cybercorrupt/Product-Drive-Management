-- Product Drive CMS — minimum seed data for testing
-- Run AFTER schema.sql:  mysql -u <user> -p <database> < sql/seed.sql
-- NOTE: password hashes below are bcrypt of the demo passwords.
--   admin@productdrive.test    / admin123
--   operator@productdrive.test / operator123
-- Change these immediately in production (via the Profile / Users screens).

SET NAMES utf8mb4;

INSERT INTO users (name, email, password_hash, role, status) VALUES
('Site Administrator', 'admin@productdrive.test', '$2y$10$FoNj/XQOGKKrO.tJ49vZX.LmT4pGOy8YuqEU5k4IkczC7ek9Li7RW', 'admin', 'active'),
('Operator One',       'operator@productdrive.test', '$2y$10$R9dSTqY32fget/xjM37BmeoLS0q0WF4xntwML./C81K5k.JXKslvC', 'operator', 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO categories (name, slug, description) VALUES
('Tech & Hardware', 'tech-hardware', 'Laptops, desktops and computing gear'),
('Mobile Devices', 'mobile-devices', 'Smartphones and tablets'),
('Wearables', 'wearables', 'Smartwatches and fitness trackers'),
('Audio', 'audio', 'Headphones, earbuds and speakers')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (name, sku, category_id, price, stock, status, image, description, created_by) VALUES
('Minimalist Pro Laptop', 'PD-1001', 1, 1499.00, 12, 'active', 'https://images.unsplash.com/photo-1738707060349-c92b5b15bf80?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', '14-inch ultralight laptop with all-day battery.', 1),
('Light Blue Smartphone', 'PD-1002', 2, 799.00, 4, 'active', 'https://images.unsplash.com/photo-1778896135791-a29fff9b8e45?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Flagship phone with a triple-camera system.', 1),
('Minimalist Smartwatch', 'PD-1003', 3, 249.00, 0, 'active', 'https://images.unsplash.com/photo-1760520338254-1bb9327ca51c?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Health tracking and notifications on your wrist.', 1),
('Wireless Earbuds', 'PD-1004', 4, 129.00, 40, 'active', 'https://images.unsplash.com/photo-1757168120889-4317e57a4849?crop=entropy&cs=srgb&fm=jpg&q=85&w=400', 'Noise-cancelling earbuds with wireless charging.', 2),
('Standing Desk Converter', 'PD-1005', 1, 189.00, 2, 'draft', NULL, 'Adjustable sit-stand desk riser.', 2)
ON DUPLICATE KEY UPDATE name = VALUES(name);
