<?php


trait ApiTestTrait
{
    private $resp;
    public function assertApiResponse(array $actualData)
    {
        $this->assertApiSuccess();

        $resp = json_decode($this->response->getContent(), true);
        $respData = $resp['data'];

        $this->assertNotEmpty($respData['id']);
        $this->assertModelData($actualData, $respData);
    }

    public function assertApiSuccess()
    {
        $this->response->assertStatus(200);
        $this->response->assertJson(['success' => true]);
    }

    public function assertModelData(array $actualData, array $expectedData)
    {
        foreach ($actualData as $key => $value) {
            if (in_array($key, ['created_at', 'updated_at'])) {
                continue;
            }
            $this->assertEquals($actualData[$key], $expectedData[$key]);
        }
    }
}
