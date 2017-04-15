<?php

class m131023_080816_alter_image_table extends CDbMigration
{
    public function up()
    {
        $this->addForeignKey('fk_image_file', 'image', 'fileId', 'file', 'id');
    }

    public function down()
    {
        $this->dropForeignKey('fk_image_file', 'image');
    }
}