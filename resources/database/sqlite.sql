-- #!sqlite

-- #{ table
    -- #{ players
        CREATE TABLE IF NOT EXISTS players
        (
            player         VARCHAR(32) PRIMARY KEY NOT NULL,
            purchasedKits  TEXT    DEFAULT "[]",
            selectedKit    TEXT    DEFAULT NULL
        );
    -- #}
    -- #{ kits
        CREATE TABLE IF NOT EXISTS kits
        (
            kit            PRIMARY KEY NOT NULL,
            price          NUMERIC DEFAULT 0,
            description    TEXT,
            permission     TEXT,
            inventoryItems TEXT,
            armorInventoryItems TEXT,
            offHandItem    TEXT
        );
    -- #}
-- #}

-- #{ data
    -- #{ players
        -- #{ add
            -- # :player string
            -- # :purchasedKits string "[]"
            -- # :selectedKit string null
            INSERT OR IGNORE INTO players(player, purchasedKits, selectedKit)
            VALUES (:player, :purchasedKits, :selectedKit);
        -- #}
        -- #{ get
            -- # :player string
            SELECT * FROM players WHERE player = :player;
        -- #}
        -- #{ getAll
            SELECT * FROM players;
        -- #}
        -- #{ setPurchasedKits
            -- # :player string
            -- # :purchasedKits string
            UPDATE players SET purchasedKits = :purchasedKits WHERE player = :player;
        -- #}
        -- #{ setSelectedKit
            -- # :player string
            -- # :selectedKit string
            UPDATE players SET selectedKit = :selectedKit WHERE player = :player;
        -- #}
        -- #{ set
            -- # :player string
            -- # :purchasedKits string
            -- # :selectedKit string
            INSERT OR REPLACE INTO
            players(player, purchasedKits, selectedKit)
            VALUES (:player, :purchasedKits, :selectedKit);
        -- #}
        -- #{ delete
            -- # :player string
            DELETE FROM players WHERE player = :player;
        -- #}
    -- #}
    -- #{ kits
        -- #{ get
            -- # :kit string
            SELECT * FROM kits WHERE kit = :kit;
        -- #}
        -- #{ getAll
            SELECT * FROM kits;
        -- #}
        -- #{ set
            -- # :kit string
            -- # :price float
            -- # :description string
            -- # :permission string
            -- # :inventoryItems string
            -- # :armorInventoryItems string
            -- # :offHandItem string
            INSERT OR REPLACE INTO
            kits(kit, price, description, permission, inventoryItems, armorInventoryItems, offHandItem)
            VALUES (:kit, :price, :description, :permission, :inventoryItems, :armorInventoryItems, :offHandItem);
        -- #}
        -- #{ delete
            -- # :kit string
            DELETE FROM kits WHERE kit = :kit;
        -- #}
    -- #}
-- #}