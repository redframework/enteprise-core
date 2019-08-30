<?php
/** Red Framework
 * Picture Class
 *
 * Operations Can be Done on a Picture
 * Secure Uploading Images
 * Image Resizing Function
 *
 * @author RedCoder
 * http://redframework.ir
 */

namespace Red\PictureTools;


class Picture
{

    /**
     * @param $file
     * @param $upload_path
     * @param $new_file_name
     * @param $target_width
     * @param $target_height
     */
    public static function uploadImage($file, $upload_path, $new_file_name, $target_width, $target_height){
        if ($file == TRUE) {

            $file_temp = $file['tmp_name'];
            $source_properties = getimagesize($file_temp);
            $image_type = $file['type'];
            $pos = strrpos($image_type, '/') + 1;
            $image_type = substr($image_type, $pos);


            switch ($image_type) {

                case 'jpeg':
                    $image_resource_id = imagecreatefromjpeg($file_temp);
                    $target_layer = self::imageResize($image_resource_id, $source_properties[0], $source_properties[1], $target_width, $target_width);
                    imagejpeg($target_layer, $upload_path . $new_file_name . "." . $image_type);
                    break;



                case 'png':
                    $image_resource_id = imagecreatefrompng($file_temp);
                    $target_layer = self::imageResize($image_resource_id, $source_properties[0], $source_properties[1], $target_width, $target_height);
                    imagepng($target_layer, $upload_path . $new_file_name . "." . $image_type);
                    break;


                default:
                    break;
            }
            move_uploaded_file($new_file_name, $upload_path . $new_file_name . "." . $image_type);
        }

    }


    /**
     * @param $image_resource_id
     * @param $width
     * @param $height
     * @param $target_width
     * @param $target_height
     * @return resource
     */
    public static function imageResize($image_resource_id, $width, $height, $target_width, $target_height)
    {
        $target_layer = imagecreatetruecolor($target_width, $target_height);
        imagecopyresampled($target_layer, $image_resource_id, 0, 0, 0, 0, $target_width, $target_height, $width, $height);
        return $target_layer;
    }
}