<?php
/*
 * Shortcodes for Garner Hello child theme
 */

 add_shortcode('multi_fieldtable', 'garner_multi_fieldtable_shortcode');
function garner_multi_fieldtable_shortcode() {
    $acf_all_fields = get_field_objects();
    $acfOutputFields = array();

$mf_plan_number = $acf_all_fields['MF_plan_number']['value'];
$mf_designer_plan_num = $acf_all_fields['mf_designer_plan_num']['value'];
$mf_unit_bedrooms = $acf_all_fields['MF-unit-bedrooms']['value'];
$mf_unit_bathrooms = $acf_all_fields['MF-unit-bathrooms']['value'];
$mf_width_feet = $acf_all_fields['MF-width-feet']['value'];
$mf_width_inches = $acf_all_fields['mf-width-inches']['value'];
$mf_depth_feet = $acf_all_fields['mf-depth-feet']['value'];
$mf_depth_inches = $acf_all_fields['mf-depth-inches']['value'];
$mf_foundation_slab = $acf_all_fields['mf-foundation_slab']['value'];
$mf_foundation_crawl_space = $acf_all_fields['mf-foundation_crawl_space']['value'];
$mf_foundation_basement_add = $acf_all_fields['mf-foundation_basement_add']['value'];
$mf_foundation_pier = $acf_all_fields['mf-foundation_pier']['value'];
$mf_foundation_basement_std = $acf_all_fields['mf-foundation_basement_std']['value'];
$mf_main_ceiling_feet = $acf_all_fields['mf-main-ceiling-feet']['value'];
$mf_main_ceiling_inches = $acf_all_fields['mf-main-ceiling-inches']['value'];
$mf_second_ceiling_feet = $acf_all_fields['mf-second-ceiling-feet']['value'];
$mf_second_ceiling_inches = $acf_all_fields['mf-second-ceiling-inches']['value'];
$mf_roof_pitch = $acf_all_fields['mf-roof-pitch']['value'];
$mf_2nd_roof_pitch = $acf_all_fields['mf-2nd-roof-pitch']['value'];
$mf_bldg_floors = $acf_all_fields['mf-bldg-floors']['value'];
$mf_unit_floors = $acf_all_fields['mf-unit-floors']['value'];
$mf_first_floor_area = $acf_all_fields['MF_first-floor-area']['value'];
$mf_2nd_floor_area = $acf_all_fields['mf-2nd-floor-area']['value'];
$mf_bonus_floor_area = $acf_all_fields['mf-bonus-floor-area']['value'];
$mf_area_heated = $acf_all_fields['mf-area_heated']['value'];
$mf_total_living_area = $acf_all_fields['mf-total-living-area']['value'];
$mf_total_area = $acf_all_fields['mf-total-area']['value'];
$mf_per_unit_garage_bays = $acf_all_fields['mf-per-unit-garage-bays']['value'];
$mf_garage_type = $acf_all_fields['mf-garage-type']['value'];

$rrFields = array(
    'Plan Number' => $mf_plan_number,
    'Designer Plan Number' => $mf_designer_plan_num,
    'Unit Bedrooms' => $mf_unit_bedrooms,
    'Unit Bathrooms' => $mf_unit_bathrooms,
    'Plan Width' => $mf_width_feet."'".$mf_width_inches.'"',
    'Plan Depth' => $mf_depth_feet."'".$mf_depth_inches.'"',
    'Foundation Slab' => $mf_foundation_slab,
    'Foundation Crawl Space' => $mf_foundation_crawl_space,
    'Foundation Basement Add' => $mf_foundation_basement_add,
    'Foundation Pier' => $mf_foundation_pier,
    'Foundation Basement Std' => $mf_foundation_basement_std,
    'Main Ceiling' => ($mf_main_ceiling_feet > 0 || $mf_main_ceiling_inches >0) ? (($mf_main_ceiling_feet >0) ? $mf_main_ceiling_feet."' ":'') . (($mf_main_ceiling_inches >0) ? $mf_main_ceiling_inches.'"':'') : '',
    'Second Ceiling' => ($mf_second_ceiling_feet > 0 || $mf_second_ceiling_inches >0) ? (($mf_second_ceiling_feet >0) ? $mf_second_ceiling_feet."' ":'') . (($mf_second_ceiling_inches >0) ? $mf_second_ceiling_inches.'"':'') : '',
    'Roof Pitch' => $mf_roof_pitch,
    '2nd Roof Pitch' => $mf_2nd_roof_pitch,
    'Bldg Floors' => $mf_bldg_floors,
    'Unit Floors' => $mf_unit_floors,
    'First Floor Area' => $mf_first_floor_area." sq.ft",
    '2nd Floor Area' => $mf_2nd_floor_area." sq.ft",
    'Bonus Floor Area' => $mf_bonus_floor_area." sq.ft",
    'Area Heated' => $mf_area_heated." sq.ft",
    'Total Living Area' => $mf_total_living_area." sq.ft",
    'Total Area' => $mf_total_area." sq.ft",
    'Per Unit Garage Bays' => $mf_per_unit_garage_bays,
    'Garage Type' => $mf_garage_type
);

    ob_start();

        $fieldcount = 0;
          
        foreach ($rrFields as $field => $value) {
            
            if($field!== 'Plan Number' && isset($value) && $value !=='-' && $value !== 'None' && $value !== '' && $value !== '0'&& $value !== '0 sq.ft'  && !is_array($value)) {
            
            $fieldcount++;
            $acfOutputFields[$field] = $value;
            }
        }
    ?>
    <div class="garner-multi-field-table-container">
        <div class="garner-multi-field-table-column">
            <table class="garner-multi-field-table">
                <?php
                $fieldcountHalf = ceil($fieldcount / 2);
                $i = 0;

                foreach($acfOutputFields as $name => $value) {
                    echo '<tr class="garner-multi-field-table-row">';
                    echo '<td class="garner-multi-field-table-label-cell">' . $name . ':</td>';
                    echo '<td class="garner-multi-field-table-value-cell">' . $value . '</td>';
                    echo '</tr>';
                    $i++;
                if($i == $fieldcountHalf) {
                    echo '</table></div><div class="garner-multi-field-table-column"><table class="garner-multi-field-table">';
                }
                

            }

            ?>
            </table>
        </div>
    </div>
    <?php
   return ob_get_clean();
}