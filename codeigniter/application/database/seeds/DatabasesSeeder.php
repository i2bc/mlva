<?php

class DatabasesSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('databases');

        $data = [
            'name' => 'Bactérie lambda',
            'user_id' => 3,
            'group_id' => 4,
            'marker_num' => 2,
            'metadatas' => '["location", "species"]',
            'datas' => '["Bruce00-1322", "Bruce12-73", "Bruce55-2066"]',
            'state' => 1,
        ];

        $this->db->insert('databases', $data);
    }
}
