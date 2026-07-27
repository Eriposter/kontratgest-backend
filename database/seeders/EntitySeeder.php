<?php

namespace Database\Seeders;

use App\Domain\Entities\Models\Entity;
use App\Support\Enums\EntityType;
use App\Support\Enums\Province;
use Illuminate\Database\Seeder;

class EntitySeeder extends Seeder
{
    public function run(): void
    {
        $entities = [
            // Bancos
            [
                'entity_type' => EntityType::SUPPLIER,
                'name' => 'Banco Angolano de Investimentos (BAI)',
                'legal_name' => 'Banco Angolano de Investimentos, S.A.',
                'nif' => '5416881284',
                'email' => 'geral@bai.ao',
                'phone' => '+244 222 641 000',
                'address' => 'Rua Major Kanhangulo, 8',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BAI',
                        'iban' => 'AO06004000001234567890123',
                        'account_holder' => 'BAI, S.A.',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(6),
                'inss_certificate_expiry' => now()->addMonths(6),
                'tax_regime' => 'general',
            ],
            [
                'entity_type' => EntityType::SUPPLIER,
                'name' => 'Banco de Fomento Angola (BFA)',
                'legal_name' => 'Banco de Fomento Angola, S.A.',
                'nif' => '5416543210',
                'email' => 'info@bfa.ao',
                'phone' => '+244 222 391 000',
                'address' => 'Avenida 4 de Fevereiro, 102',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BFA',
                        'iban' => 'AO06000600009876543210123',
                        'account_holder' => 'BFA, S.A.',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(4),
                'inss_certificate_expiry' => now()->addMonths(4),
                'tax_regime' => 'general',
            ],

            // Petróleo e Gás
            [
                'entity_type' => EntityType::CLIENT,
                'name' => 'Sonangol EP',
                'legal_name' => 'Sociedade Nacional de Combustíveis de Angola, EP',
                'nif' => '5416000001',
                'email' => 'geral@sonangol.ao',
                'phone' => '+244 222 339 000',
                'address' => 'Avenida Marginal, Luanda',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BAI',
                        'iban' => 'AO0600400000999888777666555',
                        'account_holder' => 'Sonangol EP',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(12),
                'inss_certificate_expiry' => now()->addMonths(12),
                'tax_regime' => 'general',
            ],

            // Construtoras
            [
                'entity_type' => EntityType::CONTRACTOR,
                'name' => 'Omateque Engenharia, Lda',
                'legal_name' => 'Omateque Engenharia e Construção, Lda',
                'nif' => '5417123456',
                'email' => 'info@omateque.ao',
                'phone' => '+244 222 445 678',
                'address' => 'Rua da Missão, 45',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BIC',
                        'iban' => 'AO0600230000111222333444555',
                        'account_holder' => 'Omateque Engenharia, Lda',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(3),
                'inss_certificate_expiry' => now()->addMonths(3),
                'tax_regime' => 'general',
            ],
            [
                'entity_type' => EntityType::CONTRACTOR,
                'name' => 'China Geo Engineering Angola',
                'legal_name' => 'China Geo Engineering Corporation Angola',
                'nif' => '5417987654',
                'email' => 'angola@chinageo.cn',
                'phone' => '+244 222 556 789',
                'address' => 'Zona Industrial de Viana',
                'city' => 'Viana',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BFA',
                        'iban' => 'AO0600060000555666777888999',
                        'account_holder' => 'China Geo Engineering Angola',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(5),
                'inss_certificate_expiry' => now()->addMonths(5),
                'tax_regime' => 'general',
            ],

            // Fornecedores
            [
                'entity_type' => EntityType::SUPPLIER,
                'name' => 'Cervejeira Nacional (EKA)',
                'legal_name' => 'Cervejeira Nacional, S.A.',
                'nif' => '5416234567',
                'email' => 'vendas@eka.ao',
                'phone' => '+244 222 667 890',
                'address' => 'Estrada de Catete, Km 12',
                'city' => 'Viana',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BAI',
                        'iban' => 'AO0600400000222333444555666',
                        'account_holder' => 'Cervejeira Nacional, S.A.',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(8),
                'inss_certificate_expiry' => now()->addMonths(8),
                'tax_regime' => 'general',
            ],
            [
                'entity_type' => EntityType::SUPPLIER,
                'name' => 'Distribuidora Angolana de Materiais, Lda',
                'legal_name' => 'DAM - Distribuidora Angolana de Materiais de Construção, Lda',
                'nif' => '5417345678',
                'email' => 'comercial@dam.ao',
                'phone' => '+244 222 778 901',
                'address' => 'Rua do Comércio, 123',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BFA',
                        'iban' => 'AO0600060000333444555666777',
                        'account_holder' => 'DAM, Lda',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(2), // A expirar em breve!
                'inss_certificate_expiry' => now()->addMonths(2),
                'tax_regime' => 'general',
            ],

            // Consultores
            [
                'entity_type' => EntityType::CONSULTANT,
                'name' => 'Deloitte Angola',
                'legal_name' => 'Deloitte & Associados, SROC, Lda',
                'nif' => '5417456789',
                'email' => 'angola@deloitte.com',
                'phone' => '+244 222 889 012',
                'address' => 'Edificio Talatona Tower, Torre Sul',
                'city' => 'Talatona',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'Standard Bank',
                        'iban' => 'AO0600550000444555666777888',
                        'account_holder' => 'Deloitte Angola',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->addMonths(10),
                'inss_certificate_expiry' => now()->addMonths(10),
                'tax_regime' => 'general',
            ],

            // Entidade Pública
            [
                'entity_type' => EntityType::PUBLIC_ENTITY,
                'name' => 'Ministério das Obras Públicas',
                'legal_name' => 'República de Angola - Ministério das Obras Públicas',
                'nif' => '5416000002',
                'email' => 'geral@mop.gov.ao',
                'phone' => '+244 222 334 567',
                'address' => 'Largo do Kinaxixi',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [],
                'agt_certificate_expiry' => null,
                'inss_certificate_expiry' => null,
                'is_tax_exempt' => true,
                'tax_regime' => 'exempt',
            ],

            // Entidade com certidões expiradas (para testar alertas)
            [
                'entity_type' => EntityType::SUPPLIER,
                'name' => 'Tecnologias de Angola, Lda',
                'legal_name' => 'Tecnologias de Angola, Lda',
                'nif' => '5417567890',
                'email' => 'info@techangola.ao',
                'phone' => '+244 222 990 123',
                'address' => 'Rua da Tecnologia, 456',
                'city' => 'Luanda',
                'province' => Province::LUANDA,
                'bank_accounts' => [
                    [
                        'bank' => 'BAI',
                        'iban' => 'AO0600400000666777888999000',
                        'account_holder' => 'Tecnologias de Angola, Lda',
                        'is_default' => true,
                    ],
                ],
                'agt_certificate_expiry' => now()->subDays(10), // EXPIRADA!
                'inss_certificate_expiry' => now()->subDays(5), // EXPIRADA!
                'tax_regime' => 'general',
                'status' => 'suspended',
            ],
        ];

        foreach ($entities as $entityData) {
            Entity::firstOrCreate(
                ['nif' => $entityData['nif']],
                $entityData
            );
        }

        $this->command->info('✅ ' . count($entities) . ' entidades criadas com sucesso!');
    }
}