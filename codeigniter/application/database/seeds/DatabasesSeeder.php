<?php

class DatabasesSeeder extends Seeder {

    public function run()
    {
        $this->db->truncate('strains');
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

        $data = [
            'name' => 'Poulet',
            'database_id' => 1,
            'metadatas' => '{"location":"Turkey"}',
            'datas' => '{"Bruce00-1322":2, "Bruce12-73":5, "Bruce55-2066":1}',
        ];

        $this->db->insert('strains', $data);

        $data = [
            'name' => 'Poule',
            'database_id' => 1,
            'metadatas' => '{"location":"France","species":"cow"}',
            'datas' => '{"Bruce00-1322":1, "Bruce12-73":3, "Bruce55-2066":4}',
        ];

        $this->db->insert('strains', $data);

        $data = [
            'name' => 'Cocotte',
            'database_id' => 1,
            'metadatas' => '{"location":"England","species":"cow"}',
            'datas' => '{"Bruce00-1322":1, "Bruce12-73":4, "Bruce55-2066":1}',
        ];

        $this->db->insert('strains', $data);
    }
}
