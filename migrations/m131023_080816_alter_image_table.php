<?php

class m131023_080816_alter_image_table extends CDbMigration
{
    public function up()
    {
        $this->addForeignKey('image_fileId', 'image', 'fileId', 'file', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('image_fileId', 'image');
    }
}