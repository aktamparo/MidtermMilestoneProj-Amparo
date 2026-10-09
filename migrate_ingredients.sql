USE barangay_kusina;

-- Run this once if the database was created with the original recipe-specific ingredients table.
ALTER TABLE recipes ADD COLUMN ingredient_ids JSON NULL AFTER instructions;
ALTER TABLE ingredients DROP FOREIGN KEY fk_ingredients_recipe;
RENAME TABLE ingredients TO ingredients_old;

CREATE TABLE ingredients (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO ingredients (name)
SELECT DISTINCT TRIM(name) FROM ingredients_old WHERE TRIM(name) <> '';

UPDATE recipes r
SET ingredient_ids = (
    SELECT COALESCE(CONCAT('[', GROUP_CONCAT(i.id ORDER BY i.id SEPARATOR ','), ']'), JSON_ARRAY())
    FROM ingredients_old old_ingredient
    JOIN ingredients i ON i.name = old_ingredient.name
    WHERE old_ingredient.recipe_id = r.id
);

UPDATE recipes SET ingredient_ids = JSON_ARRAY() WHERE ingredient_ids IS NULL;
ALTER TABLE recipes MODIFY ingredient_ids JSON NOT NULL;
DROP TABLE ingredients_old;
