<?php

class GroupsSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('groups');

        $data = [
            'id' => 1,
            'name' => 'Administrator',
            'permissions' => '{"admin":1}',
            'label' => 'primary',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 2,
            'name' => 'Moderator',
            'permissions' => '{"moderator":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 3,
            'name' => 'MLVA Team',
            'permissions' => '{"databases.export":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 4,
            'name' => 'User',
            'permissions' => '{"databases.create":1, "databases.import":1, "databases.export":1}',
        ];

        $this->db->insert('groups', $data);

        $data = [
            'id' => 5,
            'name' => 'Guest',
            'permissions' => '{"databases.show":0}',
        ];
        $this->db->insert('groups', $data);

        $this->db->truncate('user_has_group');

        $data = [
            'user_id' => 0,
            'group_id' => 5,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 3,
            'group_id' => 1,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 4,
            'group_id' => 1,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 1,
            'group_id' => 2,
        ];

        $this->db->insert('user_has_group', $data);

        $data = [
            'user_id' => 2,
            'group_id' => 3,
        ];

        $this->db->insert('user_has_group', $data);

    }
}
