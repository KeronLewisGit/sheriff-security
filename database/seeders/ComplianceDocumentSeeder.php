<?php

namespace Database\Seeders;

use App\Enums\ComplianceCategory;
use App\Models\ComplianceDocument;
use Illuminate\Database\Seeder;

/**
 * The documents procurement teams usually ask a Guyanese security contractor for.
 * These are empty slots: Sheriff uploads each file in Admin → Compliance documents.
 */
class ComplianceDocumentSeeder extends Seeder
{
    /** @var list<array{0: string, 1: ComplianceCategory, 2: string}> */
    public const array SLOTS = [
        ['ISO 9001:2015 certificate', ComplianceCategory::Certification, 'Quality management system'],
        ['ISO 45001:2018 certificate', ComplianceCategory::Certification, 'Occupational health & safety management system'],
        ['Local Content Secretariat certificate', ComplianceCategory::Registration, 'Local Content Register'],
        ['HSE policy statement', ComplianceCategory::Hse, 'Signed health, safety & environment policy'],
        ['Quality policy statement', ComplianceCategory::Hse, 'Signed quality policy'],
        ['Insurance certificate', ComplianceCategory::Insurance, 'Liability cover summary'],
        ['GRA tax compliance certificate', ComplianceCategory::Registration, 'Guyana Revenue Authority'],
        ['NIS compliance certificate', ComplianceCategory::Registration, 'National Insurance Scheme'],
    ];

    public function run(): void
    {
        if (ComplianceDocument::query()->exists()) {
            return;
        }
        foreach (self::SLOTS as $i => [$title, $category, $description]) {
            ComplianceDocument::create(['title' => $title, 'category' => $category, 'description' => $description, 'sort' => ($i + 1) * 10, 'is_listed' => true]);
        }
    }
}
