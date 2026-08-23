<?php

namespace App\Services;

use App\Support\SimpleZip;

/**
 * Builds the official editable planting-request DOCX template with the MENRO header banner,
 * clean underlines, structured form sections, and labeled request fields for automated parsing.
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
        return $this->packageDocx($this->blankDocumentXml());
    }

    /**
     * Build a filled copy of the official template with applicant values.
     *
     * @param  array{
     *   project_name: string,
     *   target_trees: int|string,
     *   seedling_type: string,
     *   barangay: string,
     *   requester_name?: string,
     *   agency_name?: string,
     *   contact_number?: string,
     *   position?: string,
     *   email?: string,
     *   habitat?: string,
     *   custom_address?: string,
     *   notes?: string
     * }  $fields
     */
    public function buildFilledDocxBinary(array $fields): string
    {
        return $this->packageDocx($this->filledDocumentXml($fields));
    }

    private function packageDocx(string $documentXml): string
    {
        $headerImgPath = public_path('images/menro-header.png');
        $headerImgData = file_exists($headerImgPath)
            ? file_get_contents($headerImgPath)
            : '';

        $files = [
            '[Content_Types].xml' => $this->contentTypesXml(),
            '_rels/.rels' => $this->relsXml(),
            'word/document.xml' => $documentXml,
            'word/_rels/document.xml.rels' => $this->documentRelsXml(),
        ];

        if ($headerImgData !== '' && $headerImgData !== false) {
            $files['word/media/image1.png'] = $headerImgData;
        }

        return SimpleZip::create($files);
    }

    private function contentTypesXml(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Default Extension="png" ContentType="image/png"/>
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
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rIdHeaderImg" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/image1.png"/>
</Relationships>
XML;
    }

    private function blankDocumentXml(): string
    {
        return $this->buildFullDocumentXml([
            'requester_name' => '____________________________________________________',
            'request_date' => date('m/d/Y'),
            'agency_name' => '____________________________________',
            'contact_number' => '________________________',
            'position' => '____________________________________',
            'email' => '________________________',
            'project_name' => '________________________________________________________',
            'target_trees' => '____________________',
            'target_date' => '____________________',
            'seedling_type' => '________________________________________________________',
            'habitat' => '',
            'barangay' => '____________________________________',
            'custom_address' => '____________________________________',
            'area_size' => '____________________',
            'notes' => '',
            'is_blank' => true,
        ]);
    }

    /**
     * @param  array<string, mixed>  $fields
     */
    private function filledDocumentXml(array $fields): string
    {
        return $this->buildFullDocumentXml([
            'requester_name' => (string) ($fields['requester_name'] ?? '____________________________________________________'),
            'request_date' => (string) ($fields['request_date'] ?? date('m/d/Y')),
            'agency_name' => (string) ($fields['agency_name'] ?? '____________________________________'),
            'contact_number' => (string) ($fields['contact_number'] ?? '________________________'),
            'position' => (string) ($fields['position'] ?? '____________________________________'),
            'email' => (string) ($fields['email'] ?? '________________________'),
            'project_name' => (string) ($fields['project_name'] ?? '________________________________________________________'),
            'target_trees' => (string) ($fields['target_trees'] ?? '____________________'),
            'target_date' => (string) ($fields['target_date'] ?? '____________________'),
            'seedling_type' => (string) ($fields['seedling_type'] ?? '________________________________________________________'),
            'habitat' => (string) ($fields['habitat'] ?? ''),
            'barangay' => (string) ($fields['barangay'] ?? '____________________________________'),
            'custom_address' => (string) ($fields['custom_address'] ?? '____________________________________'),
            'area_size' => (string) ($fields['area_size'] ?? '____________________'),
            'notes' => (string) ($fields['notes'] ?? 'Community-based greening, reforestation, and ecological preservation in Tagoloan.'),
            'is_blank' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $d
     */
    private function buildFullDocumentXml(array $d): string
    {
        $hasHeaderImg = file_exists(public_path('images/menro-header.png'));

        $headerDrawing = $hasHeaderImg
            ? '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="80"/></w:pPr>'
            .'<w:r><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0">'
            .'<wp:extent cx="5760000" cy="1440000"/>'
            .'<wp:docPr id="1" name="MENRO Header"/>'
            .'<wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr>'
            .'<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            .'<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<pic:nvPicPr><pic:cNvPr id="1" name="menro-header.png"/><pic:cNvPicPr/></pic:nvPicPr>'
            .'<pic:blipFill><a:blip r:embed="rIdHeaderImg" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            .'<pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="5760000" cy="1440000"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr>'
            .'</pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>'
            : '<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="60"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="064E3B"/></w:rPr><w:t>MUNICIPAL ENVIRONMENT AND NATURAL RESOURCES OFFICE (MENRO) TAGOLOAN</w:t></w:r></w:p>';

        $esc = fn ($str) => htmlspecialchars((string) $str, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $reqName = $esc($d['requester_name']);
        $reqDate = $esc($d['request_date']);
        $agency = $esc($d['agency_name']);
        $contact = $esc($d['contact_number']);
        $position = $esc($d['position']);
        $email = $esc($d['email']);
        $projName = $esc($d['project_name']);
        $targetTrees = $esc($d['target_trees']);
        $targetDate = $esc($d['target_date']);
        $seedlingType = $esc($d['seedling_type']);
        $barangay = $esc($d['barangay']);
        $address = $esc($d['custom_address']);
        $areaSize = $esc($d['area_size']);
        $notes = $esc($d['notes']);
        $isBlank = ! empty($d['is_blank']);

        $uTag = $isBlank ? '' : '<w:u w:val="single"/>';

        $habitatTerrestrial = strcasecmp((string) $d['habitat'], 'terrestrial') === 0 ? '✓' : '  ';
        $habitatMangrove = (strcasecmp((string) $d['habitat'], 'mangrove') === 0 || strcasecmp((string) $d['habitat'], 'coastal') === 0) ? '✓' : '  ';
        $habitatRiparian = strcasecmp((string) $d['habitat'], 'riparian') === 0 ? '✓' : '  ';
        $habitatUrban = strcasecmp((string) $d['habitat'], 'urban') === 0 ? '✓' : '  ';

        $notesLines = $isBlank
            ? '<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr><w:r><w:rPr><w:sz w:val="18"/><w:color w:val="475569"/></w:rPr><w:t>____________________________________________________________________________</w:t></w:r></w:p>'
             .'<w:p><w:pPr><w:spacing w:before="30" w:after="60"/></w:pPr><w:r><w:rPr><w:sz w:val="18"/><w:color w:val="475569"/></w:rPr><w:t>____________________________________________________________________________</w:t></w:r></w:p>'
            : '<w:p><w:pPr><w:spacing w:before="30" w:after="60"/></w:pPr><w:r><w:rPr><w:u w:val="single"/><w:sz w:val="18"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$notes.'</w:t></w:r></w:p>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            .'<w:body>'

            // Header Banner
            .$headerDrawing

            // Document Title Banner
            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="40" w:after="30"/><w:shd w:val="clear" w:color="auto" w:fill="064E3B"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="22"/><w:color w:val="FFFFFF"/></w:rPr><w:t>PLANTING REQUEST &amp; SEEDLING ALLOCATION APPLICATION</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:before="0" w:after="80"/></w:pPr>'
            .'<w:r><w:rPr><w:i/><w:sz w:val="15"/><w:color w:val="059669"/></w:rPr><w:t>Official Form — Municipality of Tagoloan, Misamis Oriental</w:t></w:r></w:p>'

            // Form Control Bar
            .'<w:p><w:pPr><w:spacing w:before="0" w:after="100"/><w:shd w:val="clear" w:color="auto" w:fill="F1F5F9"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="475569"/></w:rPr><w:t>Form Control No: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="0F172A"/></w:rPr><w:t>MENRO-TAG-PR-2026   |   </w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="475569"/></w:rPr><w:t>Revision No: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="0F172A"/></w:rPr><w:t>02   |   </w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="475569"/></w:rPr><w:t>Municipality: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="0F172A"/></w:rPr><w:t>Tagoloan, Misamis Oriental</w:t></w:r>'
            .'</w:p>'

            // Section 1 Header
            .'<w:p><w:pPr><w:spacing w:before="100" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 1: APPLICANT &amp; ORGANIZATION INFORMATION</w:t></w:r></w:p>'

            // Section 1 Body
            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Name of Requester / Head: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$reqName.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Date of Request: </w:t></w:r>'
            .'<w:r><w:rPr><w:u w:val="single"/><w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$reqDate.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Agency / Organization: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$agency.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Contact Number: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$contact.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Designation / Position: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$position.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Email Address: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$email.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="60"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Requester Category: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="17"/><w:color w:val="475569"/></w:rPr><w:t>[  ] Individual / Family   [  ] LGU / Barangay   [  ] NGO / Civil Society   [  ] School / University   [  ] Corporate</w:t></w:r></w:p>'

            // Section 2 Header
            .'<w:p><w:pPr><w:spacing w:before="100" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 2: PLANTING PROJECT SPECIFICATIONS</w:t></w:r></w:p>'

            // Section 2 Body with exact parser labels and underlines
            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>Project Name: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:b/><w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$projName.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Target Trees: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$targetTrees.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Proposed Planting Date: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$targetDate.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Type of Seedling: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$seedlingType.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Planting Habitat / Zone: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="17"/><w:color w:val="475569"/></w:rPr><w:t>['.$habitatTerrestrial.'] Terrestrial Forest / Upland   ['.$habitatMangrove.'] Mangrove / Coastal   ['.$habitatRiparian.'] Riparian / Riverbank   ['.$habitatUrban.'] Urban Greening</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="20"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Notes / Purpose:</w:t></w:r></w:p>'
            .$notesLines

            // Section 3 Header
            .'<w:p><w:pPr><w:spacing w:before="100" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 3: PLANTING SITE &amp; LOCATION DETAILS</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Barangay: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:b/><w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$barangay.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Municipality: </w:t></w:r>'
            .'<w:r><w:rPr><w:u w:val="single"/><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>Tagoloan, Misamis Oriental</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="30"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Sitio / Street / Landmark: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$address.'</w:t></w:r>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>      Area Size: </w:t></w:r>'
            .'<w:r><w:rPr>'.$uTag.'<w:sz w:val="17"/><w:color w:val="0F172A"/></w:rPr><w:t>'.$areaSize.'</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="30" w:after="60"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="334155"/></w:rPr><w:t>Land Tenure / Ownership: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="17"/><w:color w:val="475569"/></w:rPr><w:t>[  ] Public / Government Land   [  ] Community Domain   [  ] School / Institutional   [  ] Private Land (with consent)</w:t></w:r></w:p>'

            // Section 4 Header
            .'<w:p><w:pPr><w:spacing w:before="100" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 4: SEEDLING SPECIES ALLOCATION BREAKDOWN</w:t></w:r></w:p>'

            // Table of Seedlings
            .'<w:tbl>'
            .'<w:tblPr><w:tblW w:w="9360" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:left w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:right w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/></w:tblBorders></w:tblPr>'
            // Table Header Row
            .'<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="600" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>#</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr><w:p><w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>Seedling Species Requested (e.g. Narra, Mahogany, Bakauan, Molave)</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>Qty Requested</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>Approved Qty</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr><w:p><w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>Remarks</w:t></w:r></w:p></w:tc>'
            .'</w:tr>'
            // Row 1
            .'<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="600" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>1</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>_____________________________________________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'</w:tr>'
            // Row 2
            .'<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="600" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>2</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>_____________________________________________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'</w:tr>'
            // Row 3
            .'<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="600" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>3</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>_____________________________________________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'</w:tr>'
            // Row 4
            .'<w:tr>'
            .'<w:tc><w:tcPr><w:tcW w:w="600" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>4</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="4200" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>_____________________________________________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1500" w:type="dxa"/></w:tcPr><w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'<w:tc><w:tcPr><w:tcW w:w="1560" w:type="dxa"/></w:tcPr><w:p><w:r><w:rPr><w:sz w:val="15"/></w:rPr><w:t>____________</w:t></w:r></w:p></w:tc>'
            .'</w:tr>'
            .'</w:tbl>'

            // Section 5 Header
            .'<w:p><w:pPr><w:spacing w:before="120" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 5: POST-PLANTING CARE &amp; MONITORING AGREEMENT</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>• Maintenance Commitment: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="334155"/></w:rPr><w:t>The requesting party agrees to conduct regular weeding, watering, and protective fencing for the planted seedlings for a minimum of twelve (12) months.</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>• Mortality Monitoring: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="334155"/></w:rPr><w:t>Seedling survival status and mortality counts must be documented and reported to MENRO Tagoloan for replanting coordination.</w:t></w:r></w:p>'

            .'<w:p><w:pPr><w:spacing w:before="20" w:after="20"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="15"/><w:color w:val="064E3B"/></w:rPr><w:t>• Periodic Inspection: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="15"/><w:color w:val="334155"/></w:rPr><w:t>The site shall be accessible for scheduled field validation by MENRO Forestry &amp; Monitoring technical inspectors.</w:t></w:r></w:p>'

            // Section 6 Signatures Header
            .'<w:p><w:pPr><w:spacing w:before="120" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="E2E8F0"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="17"/><w:color w:val="064E3B"/></w:rPr><w:t>SECTION 6: SIGNATURES &amp; OFFICIAL APPROVALS</w:t></w:r></w:p>'

            // Signatures Table
            .'<w:tbl>'
            .'<w:tblPr><w:tblW w:w="9360" w:type="dxa"/><w:tblBorders><w:top w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:left w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:bottom w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:right w:val="single" w:sz="4" w:space="0" w:color="CBD5E1"/><w:insideH w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/><w:insideV w:val="single" w:sz="4" w:space="0" w:color="E2E8F0"/></w:tblBorders></w:tblPr>'
            .'<w:tr>'
            // Card 1
            .'<w:tc><w:tcPr><w:tcW w:w="2340" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>'
            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="140"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="14"/><w:color w:val="064E3B"/></w:rPr><w:t>1. PREPARED BY</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="14"/><w:color w:val="0F172A"/></w:rPr><w:t>________________________</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="13"/><w:color w:val="64748B"/></w:rPr><w:t>Requester Signature</w:t></w:r></w:p>'
            .'</w:tc>'
            // Card 2
            .'<w:tc><w:tcPr><w:tcW w:w="2340" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>'
            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="140"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="14"/><w:color w:val="064E3B"/></w:rPr><w:t>2. ENDORSED BY</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="14"/><w:color w:val="0F172A"/></w:rPr><w:t>________________________</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="13"/><w:color w:val="64748B"/></w:rPr><w:t>Barangay Captain / Head</w:t></w:r></w:p>'
            .'</w:tc>'
            // Card 3
            .'<w:tc><w:tcPr><w:tcW w:w="2340" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>'
            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="140"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="14"/><w:color w:val="064E3B"/></w:rPr><w:t>3. INSPECTED BY</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="14"/><w:color w:val="0F172A"/></w:rPr><w:t>________________________</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="13"/><w:color w:val="64748B"/></w:rPr><w:t>Technical Inspector</w:t></w:r></w:p>'
            .'</w:tc>'
            // Card 4
            .'<w:tc><w:tcPr><w:tcW w:w="2340" w:type="dxa"/><w:shd w:val="clear" w:color="auto" w:fill="F8FAFC"/></w:tcPr>'
            .'<w:p><w:pPr><w:jc w:val="center"/><w:spacing w:after="140"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="14"/><w:color w:val="064E3B"/></w:rPr><w:t>4. APPROVED BY</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="14"/><w:color w:val="0F172A"/></w:rPr><w:t>________________________</w:t></w:r></w:p>'
            .'<w:p><w:pPr><w:jc w:val="center"/></w:pPr><w:r><w:rPr><w:sz w:val="13"/><w:color w:val="64748B"/></w:rPr><w:t>MENRO Officer</w:t></w:r></w:p>'
            .'</w:tc>'
            .'</w:tr>'
            .'</w:tbl>'

            // Notice & Footer
            .'<w:p><w:pPr><w:spacing w:before="100" w:after="40"/><w:shd w:val="clear" w:color="auto" w:fill="ECFDF5"/></w:pPr>'
            .'<w:r><w:rPr><w:b/><w:sz w:val="14"/><w:color w:val="065F46"/></w:rPr><w:t>Submission Guidelines: </w:t></w:r>'
            .'<w:r><w:rPr><w:sz w:val="14"/><w:color w:val="065F46"/></w:rPr><w:t>Type your details over the underlines above or print and fill out. Submit completed application to MENRO Tagoloan, Municipal Hall, Tagoloan, Misamis Oriental or upload via the Tree Monitoring Web Portal. Keep field labels intact for automated OCR extraction.</w:t></w:r></w:p>'

            .'<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080"/></w:sectPr>'
            .'</w:body></w:document>';
    }
}
