<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'=>[
                'type'=>'INT',
                'constraint'=>11,
                'unsigned'=>true,
                'auto_increment'=>true
            ],
            'name'=>[
                'type'=>'VARCHAR',
                'constraint'=>255
            ],
            'username'=>[
                'type'=>'VARCHAR',
                'constraint'=>255
            ],
            'email'=>[
                'type'=>'VARCHAR',
                'constraint'=>255
            ],
            'password'=>[
                'type'=>'VARCHAR',
                'constraint'=>255
            ],
            'picture'=>[
                'type'=>'VARCHAR',
                'constraint'=>255,
                'null'=>true
            ],
            'bio'=>[
                'type'=>'TEXT',
                'null'=>true
            ],
            'created_at datetime default current_timestamp',
            'updated_at current'=>[
                'type'=>'DATETIME',
                'null'=>true
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
    }
}
