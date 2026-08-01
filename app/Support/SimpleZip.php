<?php

namespace App\Support;

/**
 * Minimal ZIP helper that does not require the php-zip extension.
 * Supports creating archives with stored (uncompressed) entries and reading
 * stored or deflated entries — enough for DOCX generate/extract.
 */
class SimpleZip
{
    /**
     * @param  array<string, string>  $files  path => contents
     */
    public static function create(array $files): string
    {
        $central = '';
        $offset = 0;
        $count = 0;
        $body = '';

        foreach ($files as $name => $contents) {
            $name = str_replace('\\', '/', $name);
            $nameLen = strlen($name);
            $size = strlen($contents);
            $crc = crc32($contents);
            // PHP crc32 returns signed on some builds; pack as unsigned.
            $crc = $crc < 0 ? $crc + 0x100000000 : $crc;

            $local = pack('VvvvvvVVVvv',
                0x04034b50, // local file header signature
                20,         // version needed
                0,          // flags
                0,          // compression: store
                0,          // mod time
                0,          // mod date
                $crc,
                $size,
                $size,
                $nameLen,
                0           // extra length
            ).$name.$contents;

            $body .= $local;

            $central .= pack('VvvvvvvVVVvvvvvVV',
                0x02014b50, // central directory header
                20,         // version made by
                20,         // version needed
                0,          // flags
                0,          // compression
                0,          // mod time
                0,          // mod date
                $crc,
                $size,
                $size,
                $nameLen,
                0,          // extra
                0,          // comment
                0,          // disk start
                0,          // internal attr
                0,          // external attr
                $offset
            ).$name;

            $offset += strlen($local);
            $count++;
        }

        $centralOffset = strlen($body);
        $centralSize = strlen($central);
        $end = pack('VvvvvVVv',
            0x06054b50,
            0,
            0,
            $count,
            $count,
            $centralSize,
            $centralOffset,
            0
        );

        return $body.$central.$end;
    }

    public static function read(string $zipBinary, string $innerPath): ?string
    {
        $innerPath = str_replace('\\', '/', $innerPath);
        $pos = 0;
        $len = strlen($zipBinary);

        while ($pos + 30 <= $len) {
            $sig = unpack('V', substr($zipBinary, $pos, 4))[1] ?? 0;
            if ($sig !== 0x04034b50) {
                break;
            }

            $header = unpack('vversion/vflags/vmethod/vtime/vdate/Vcrc/Vcomp/Vuncomp/vnameLen/vextraLen', substr($zipBinary, $pos + 4, 26));
            if ($header === false) {
                return null;
            }

            $name = substr($zipBinary, $pos + 30, $header['nameLen']);
            $dataStart = $pos + 30 + $header['nameLen'] + $header['extraLen'];
            $data = substr($zipBinary, $dataStart, $header['comp']);

            if ($name === $innerPath) {
                if ($header['method'] === 0) {
                    return $data;
                }
                if ($header['method'] === 8) {
                    $inflated = @gzinflate($data);
                    if ($inflated === false) {
                        // Some ZIP writers use zlib headers.
                        $inflated = @gzuncompress($data);
                    }
                    if ($inflated === false) {
                        $inflated = @gzdecode($data);
                    }

                    return $inflated === false ? null : $inflated;
                }

                return null;
            }

            $pos = $dataStart + $header['comp'];
        }

        return null;
    }
}
