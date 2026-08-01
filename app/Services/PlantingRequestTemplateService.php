<?php

namespace App\Services;

use App\Support\SimpleZip;

/**
 * Builds the official planting-request DOCX template with labeled request fields.
 */
class PlantingRequestTemplateService
{
    public function filename(): string
    {
        return 'MENRO-Planting-Request-Template.docx';
    }

    public function mimeType(): string
    {
        return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    }

    public function buildDocxBinary(): string
    {
        return SimpleZip::create([
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->relsXml(),
            'word/document.xml' => $this->documentXml(),
            'word/_rels/document.xml.rels' => $this->documentRelsXml(),
        ]);
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>
XML;
    }

    private function relsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML;
    }

    private function documentRelsXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>
XML;
    }

    private function documentXml(): string
    {
        $lines = [
            'MENRO TAGOLOAN — PLANTING REQUEST',
            '',
            'Fill in the blanks below. Keep the labels exactly as written so request fields can autofill.',
            '',
            'Project Name: ______________________________',
            'Target Trees: __________',
            'Barangay: ______________________________',
            'Municipality: Tagoloan',
            '',
            'Notes / Purpose:',
            '____________________________________________________________',
            '____________________________________________________________',
        ];

        $paragraphs = '';
        foreach ($lines as $line) {
            $escaped = htmlspecialchars($line, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            if ($escaped === '') {
                $paragraphs .= '<w:p><w:pPr><w:spacing w:after="120"/></w:pPr></w:p>';
                continue;
            }

            $bold = $line === 'MENRO TAGOLOAN — PLANTING REQUEST';
            $runProps = $bold
                ? '<w:rPr><w:b/><w:sz w:val="28"/></w:rPr>'
                : '<w:rPr><w:sz w:val="22"/></w:rPr>';

            $paragraphs .= '<w:p><w:pPr><w:spacing w:after="120"/></w:pPr>'
                .'<w:r>'.$runProps.'<w:t xml:space="preserve">'.$escaped.'</w:t></w:r></w:p>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:body>'.$paragraphs
            .'<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
            .'</w:body></w:document>';
    }
}
