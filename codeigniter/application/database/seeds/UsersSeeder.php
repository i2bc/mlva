<?php

class UsersSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('users');

        $data = [
            'id' => 1,
            'first_name' => 'Jon',
            'last_name' => 'Snow',
            'username' => 'jon',
            'password' => 'test',
            'email' => 'raffin@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 2,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'john',
            'password' => 'test',
            'email' => 'raffin@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 3,
            'first_name' => 'Brendan',
            'last_name' => 'Daoud',
            'username' => 'brendan',
            'password' => 'test',
            'email' => 'daoud@ensta.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);

        $data = [
            'id' => 4,
            'first_name' => 'Antonin',
            'last_name' => 'Raffin',
            'username' => 'antonin',
            'password' => 'test',
            'email' => 'antonin.raffin@ensta-paristech.fr',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('users', $data);
    }
}
