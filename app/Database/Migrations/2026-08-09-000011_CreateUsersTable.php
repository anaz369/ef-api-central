<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'name'                   => ['type' => 'VARCHAR', 'constraint' => 255],
            'email'                  => ['type' => 'VARCHAR', 'constraint' => 255],
            'password_hash'          => ['type' => 'VARCHAR', 'constraint' => 255],
            'type'                   => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0, 'comment' => '0=admin 1=super_admin'],
            'company_name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'totp_secret'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'totp_enabled'           => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'force_password_change'  => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'is_active'              => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 1],
            'last_login_at'          => ['type' => 'DATETIME', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
            'updated_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('tbl_users');

        // Seed default super admin (password: admin123, change on first login)
        $this->db->table('tbl_users')->insert([
            'name'                  => 'Super Admin',
            'email'                 => 'admin@ethicfin.com',
            'password_hash'         => password_hash('admin123', PASSWORD_BCRYPT),
            'type'                  => 1,
            'company_name'          => 'EF Intelligence L.L.C-FZ',
            'force_password_change' => 0,
            'is_active'             => 1,
            'created_at'            => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_users');
    }
}
