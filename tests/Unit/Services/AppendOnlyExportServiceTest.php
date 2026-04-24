<?php

namespace Tests\Unit\Services;

use App\Services\AppendOnlyExportService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AppendOnlyExportServiceTest extends TestCase
{
    private $testFile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testFile = storage_path('app/test_export.xlsx');
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testFile)) {
            unlink($this->testFile);
        }
        parent::tearDown();
    }

    public function test_append_records_creates_new_file()
    {
        $headers = ['Name', 'Email'];
        $newRecords = [
            ['John Doe', 'john@example.com'],
            ['Jane Doe', 'jane@example.com'],
        ];

        $result = AppendOnlyExportService::appendRecords(
            $this->testFile,
            [],
            $newRecords,
            'A',
            $headers
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['newCount']);
        $this->assertFileExists($this->testFile);
    }

    public function test_append_records_to_existing_file()
    {
        // First creation
        $headers = ['Name', 'Email'];
        $records1 = [['User 1', 'user1@example.com']];
        AppendOnlyExportService::appendRecords($this->testFile, [], $records1, 'A', $headers);

        // Append
        $records2 = [['User 2', 'user2@example.com']];
        $result = AppendOnlyExportService::appendRecords($this->testFile, [], $records2, 'A', $headers);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['newCount']);
        $this->assertFileExists($this->testFile);
    }

    public function test_append_records_with_invalid_path_fails_gracefully()
    {
        $result = AppendOnlyExportService::appendRecords(
            '/invalid/path/file.xlsx',
            [],
            [['Data']],
            'A'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('Export failed', $result['message']);
    }
}
