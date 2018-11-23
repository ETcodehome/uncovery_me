<?php

/**
 * returns the text of an enchantment based on a value in the array
 * this is used if we do not have that ALL_CAPS value but the ID for example
 * returns the value of the field
 *
 * @global array $ENCH_ITEMS
 * @param type $search_field
 * @param type $search_value
 * @param type $return_field
 * @return type
 */
function umc_enchant_text_find($search_field, $search_value, $return_field) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    global $ENCH_ITEMS;
    //TODO: This needs to be simplified. Either we find a better way to directly
    // get the text key, or we change the array once we do not need the text
    // keys anymore when NBT is fully implemented.

    // we get the numeric key
    $ench_key = array_search($search_value, array_column($ENCH_ITEMS, $search_field));
    // get the text key from the numeric
    $keys = array_keys($ENCH_ITEMS);
    $text_key = $keys[$ench_key];

    $text = $ENCH_ITEMS[$text_key][$return_field];
    return $text;
}


$ENCH_ITEMS = array(
    'aqua_affinity' => array( // 6
        'key' => 'aqua_affinity',
        'short' => 'Aqua',
        'name' => 'AquaAffinity',
        'items' => array(
            'diamond_helmet', 'golden_helmet', 'iron_helmet', 'chainmail_helmet', 'leather_helmet',
        ),
        'max' => 1
    ), 
    'bane_of_arthropods' => array( // 18
        'key' => 'bane_of_arthropods',
        'short' => 'Bane',
        'name' => 'BaneOfArthropods',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
        ),
        'max' => 5
    ),    
    'binding_curse' => array( // 10
        'key' => 'binding_curse',
        'short' => 'Binding',
        'name' => 'BindingCurse',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
            'elytra',
        ),
        'max' => 1
    ),   
    'blast_protection' => array( // 3
        'key' => 'blast_protection',
        'short' => 'BP',
        'name' => 'BlastProtection',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
        ),
        'max' => 4
    ),    
    'channeling' => array( // 62
        'key' => 'channeling',
        'short' => 'Channeling',
        'name' => 'Channeling',
        'items' => array('trident'),
        'max' => 1
    ),      
    'depth_strider' => array( // 8
        'key' => 'depth_strider',
        'short' => 'Depth',
        'name' => 'DepthStrider',
        'items' => array(
            'diamond_boots', 'golden_boots', 'iron_boots', 'chainmail_boots', 'leather_boots',
        ),
        'max' => 3
    ),    
    'efficiency' =>array( // 32
        'key' => 'efficiency',
        'short' => 'Eff',
        'name' => 'Efficiency',
        'items' => array(
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
            'shears'
        ),
        'max' => 5
    ),    
    'feather_falling' => array( // 2
        'key' => 'feather_falling',
        'short' => 'Fall',
        'name' => 'FeatherFalling',
        'items' => array(
            'diamond_boots', 'golden_boots', 'iron_boots', 'chainmail_boots', 'leather_boots',
        ),
        'max' => 4
    ),    
    'fire_aspect' => array( // 20
        'key' => 'fire_aspect',
        'short' => 'Fire',
        'name' => 'FireAspect',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
        ),
        'max' => 2
    ),    
    'fire_protection' => array( // 1
        'key' => 'fire_protection',
        'short' => 'FP',
        'name' => 'FireProtection',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
        ),
        'max' => 4
    ),  
    'flame' => array( // 50
        'key' => 'flame',
        'short' => 'Flame',
        'name' => 'Flame',
        'items' => array('bow'),
        'max' => 1
    ),    
    'fortune' => array( // 35
        'key' => 'fortune',
        'short' => 'Fort',
        'name' => 'Fortune',
        'items' => array(
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
        ),
        'max' => 3
    ),    
    'frost_walker' => array( // 9
        'key' => 'frost_walker',
        'short' => 'frostwalk',
        'name' => 'FrostWalker',
        'items' => array(
            'diamond_boots', 'golden_boots', 'iron_boots', 'chainmail_boots', 'leather_boots',
        ),
        'max' => 2
    ),   
    'infinity' => array( // 51
        'key' => 'infinity',
        'short' => 'Inf',
        'name' => 'Infinity',
        'items' => array('bow'),
        'max' => 1
    ),    
    'impaling' => array( // 62
        'key' => 'impaling',
        'short' => 'Impale',
        'name' => 'Impaling',
        'items' => array('trident'),
        'max' => 5
    ),       
    'knockback' => array( // 19
        'key' => 'knockback',
        'short' => 'Knock',
        'name' => 'Knockback',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
        ),
        'max' => 2
    ),    
    'looting' =>array( // 21
        'key' => 'looting',
        'short' => 'Loot',
        'name' => 'Looting',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'trident',
        ),
        'max' => 3
    ),    
    'loyalty' => array( // 62
        'key' => 'loyalty',
        'short' => 'Loyalty',
        'name' => 'Loyalty',
        'items' => array('trident'),
        'max' => 3
    ),     
    'luck_of_the_sea' =>array( // 61
        'key' => 'luck_of_the_sea',
        'short' => 'Luck',
        'name' => 'Luck',
        'items' => array('fishing_rod'),
        'max' => 1
    ),
    'lure' => array( // 62
        'key' => 'lure',
        'short' => 'Lure',
        'name' => 'Lure',
        'items' => array('fishing_rod'),
        'max' => 1
    ),
    'mending' => array( // 70
        'key' => 'mending',
        'short' => 'Mending',
        'name' => 'Mending',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_hoe', 'golden_hoe', 'iron_hoe', 'stone_hoe', 'wooden_hoe',
            'trident', 'bow', 'fishing_rod', 'shears', 'flint_and_steel', 'carrot_on_a_stick', 'shield', 'elytra',
        ),
        'max' => 1
    ),    
    'power' => array( //48
        'key' => 'power',
        'short' => 'Power',
        'name' => 'Power',
        'items' => array('bow'),
        'max' => 5
    ),   
    'projectile_protection' => array( // 4
        'key' => 'projectile_protection',
        'short' => 'PP',
        'name' => 'ProjectileProtection',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
        ),
        'max' => 4
    ),    
    'protection' => array( // 0
        'key' => 'protection',
        'short' => 'Prot',
        'name' => 'Protection',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
        ),
        'max' => 4
    ),
    'punch' => array( //49
        'key' => 'punch',
        'short' => 'Punch',
        'name' => 'Punch',
        'items' => array('bow'),
        'max' => 2
    ),     
    'respiration' => array( // 5
        'key' => 'respiration',
        'short' => 'Res',
        'name' => 'Respiration',
        'items' => array(
            'diamond_helmet', 'golden_helmet', 'iron_helmet', 'chainmail_helmet', 'leather_helmet',
        ),
        'max' => 3
    ),
    'riptide' => array( // 62
        'key' => 'riptide',
        'short' => 'Riptide',
        'name' => 'RipTide',
        'items' => array('trident'),
        'max' => 3
    ),     
    'sharpness' => array( // 16
        'key' => 'sharpness',
        'short' => 'Sharp',
        'name' => 'Sharpness',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
        ),
        'max' => 5
    ),
    'silk_touch' => array( // 33
        'key' => 'silk_touch',
        'short' => 'Silk',
        'name' => 'SilkTouch',
        'items' => array(
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
        ),
        'max' => 1
    ),    
    'smite' => array( // 17
        'key' => 'smite',
        'short' => 'Smite',
        'name' => 'Smite',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
        ),
        'max' => 5
    ),
    'sweeping' =>array(  // 71
        'key' => 'sweeping',
        'short' => 'Sweeping',
        'name' => 'Sweeping Edge',
        'items' => array(
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
        ),
        'max' => 1
    ),    
    'thorns' => array( // 7
        'key' => 'thorns',
        'short' => 'Thorn',
        'name' => 'Thorn',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
        ),
        'max' => 3
    ),    
    'unbreaking' => array( // 34
        'key' => 'unbreaking',
        'short' => 'Unb',
        'name' => 'Unbreaking',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_hoe', 'golden_hoe', 'iron_hoe', 'stone_hoe', 'wooden_hoe',
            'trident', 'bow', 'fishing_rod', 'shears', 'flint_and_steel', 'carrot_on_a_stick', 'shield', 'elytra'
        ),
        'max' => 3
    ),
    'vanishing_curse' =>array(  // 71
        'key' => 'vanishing_curse',
        'short' => 'Vanish',
        'name' => 'Curse of Vanishing',
        'items' => array(
            'diamond_helmet', 'diamond_chestplate', 'diamond_leggings', 'diamond_boots',
            'golden_helmet', 'golden_chestplate', 'golden_leggings', 'golden_boots',
            'iron_helmet', 'iron_chestplate', 'iron_leggings', 'iron_boots',
            'chainmail_helmet', 'chainmail_chestplate', 'chainmail_leggings', 'chainmail_boots',
            'leather_helmet', 'leather_chestplate', 'leather_leggings', 'leather_boots',
            'diamond_pickaxe', 'golden_pickaxe', 'iron_pickaxe', 'stone_pickaxe', 'wooden_pickaxe',
            'diamond_shovel', 'golden_shovel', 'iron_shovel', 'stone_shovel', 'wooden_shovel',
            'diamond_axe', 'golden_axe', 'iron_axe', 'stone_axe', 'wooden_axe',
            'diamond_sword', 'golden_sword', 'iron_sword', 'stone_sword', 'wooden_sword',
            'diamond_hoe', 'golden_hoe', 'iron_hoe', 'stone_hoe', 'wooden_hoe',
            'trident', 'bow', 'fishing_rod', 'shears', 'flint_and_steel', 'carrot_on_a_stick', 'shield', 'elytra',
        ),
        'max' => 1
    ),      
);