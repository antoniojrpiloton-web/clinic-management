<?php

namespace App\Services;

use App\Models\Backup;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupService
{
    public const DIRECTORY = 'backups';

    public function run(Backup $backup): Backup
    {
        $connection = config('database.connections.'.config('database.default'));

        if (! in_array($connection['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Database backups are only supported for MySQL/MariaDB connections.');
        }

        $disk = Storage::disk('local');
        $disk->makeDirectory(self::DIRECTORY);

        $filename = sprintf('backup-%s-%d.sql.gz', now()->format('Y-m-d-His'), $backup->id);
        $sqlPath = $disk->path(self::DIRECTORY.'/'.str_replace('.gz', '', $filename));

        $result = Process::timeout(900)
            ->env(['MYSQL_PWD' => (string) ($connection['password'] ?? '')])
            ->run([
                config('backup.mysqldump_path') ?: 'mysqldump',
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--user='.$connection['username'],
                '--single-transaction',
                '--routines',
                '--triggers',
                '--no-tablespaces',
                '--default-character-set=utf8mb4',
                '--result-file='.$sqlPath,
                $connection['database'],
            ]);

        if ($result->failed()) {
            @unlink($sqlPath);

            throw new RuntimeException('mysqldump failed: '.trim($result->errorOutput() ?: $result->output()));
        }

        $this->compress($sqlPath, $disk->path(self::DIRECTORY.'/'.$filename));

        $backup->update([
            'filename' => $filename,
            'size' => $disk->size(self::DIRECTORY.'/'.$filename),
            'status' => Backup::STATUS_COMPLETED,
            'error' => null,
            'completed_at' => now(),
        ]);

        return $backup;
    }

    public function path(Backup $backup): string
    {
        return self::DIRECTORY.'/'.$backup->filename;
    }

    public function exists(Backup $backup): bool
    {
        return $backup->filename !== null && Storage::disk('local')->exists($this->path($backup));
    }

    public function delete(Backup $backup): void
    {
        if ($backup->filename !== null) {
            Storage::disk('local')->delete($this->path($backup));
        }

        $backup->delete();
    }

    protected function compress(string $source, string $destination): void
    {
        $input = fopen($source, 'rb');
        $output = gzopen($destination, 'wb6');

        if ($input === false || $output === false) {
            throw new RuntimeException('Unable to compress the backup file.');
        }

        while (! feof($input)) {
            gzwrite($output, (string) fread($input, 1024 * 512));
        }

        fclose($input);
        gzclose($output);
        unlink($source);
    }
}
