<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Company;
use App\Entity\Address;
use App\Entity\Article;
use App\Entity\LegalForm;
use App\Entity\Service;
use App\Entity\ServiceStatus;
use App\Entity\InvoiceStatus;
use App\Entity\Subscription;
use App\Entity\SubscriptionOption;
use App\Entity\QuotationStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class AppFixtures extends Fixture implements FixtureGroupInterface
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create('fr_FR');

        $industries = [
            'Technology',
            'Healthcare',
            'Finance',
            'Education',
            'Manufacturing',
            'Retail',
            'Construction',
            'Transportation',
            'Real Estate',
            'Energy'
        ];

        // Create Legal Forms
        $legalForms = [];
        for ($i = 0; $i < 3; $i++) {
            $legalForm = new LegalForm();
            $legalForm->setName($faker->companySuffix);
            $manager->persist($legalForm);
            $legalForms[] = $legalForm;
        }

        // Create Service Statuses
        $serviceStatusesNames = ['Active', 'Inactive', 'Pending', 'Cancelled', ''];
        $serviceStatuses = [];
        foreach ($serviceStatusesNames as $statusName) {
            $status = new ServiceStatus();
            $status->setName($statusName);
            $manager->persist($status);
            $serviceStatuses[] = $status;
        }

        // Create Invoice Statuses
        $invoiceStatusesNames = ['Paid', 'Unpaid', 'Overdue', 'Cancelled', 'Pending', 'Sent', 'Refunded', 'Partially Paid'];
        $invoiceStatuses = [];
        foreach ($invoiceStatusesNames as $statusName) {
            $status = new InvoiceStatus();
            $status->setName($statusName);
            $manager->persist($status);
            $invoiceStatuses[] = $status;
        }

        // Create Quotation Statuses
        $quotationStatusesNames = ['Accepted', 'Rejected', 'Pending'];
        foreach ($quotationStatusesNames as $statusName) {
            $status = new QuotationStatus();
            $status->setName($statusName);
            $manager->persist($status);
        }

        // Create Companies with unique addresses
        $companies = [];
        for ($i = 0; $i < 5; $i++) {
            // Create Address
            $address = new Address();
            $address->setZipcode($faker->postcode);
            $address->setCity($faker->city);
            $address->setCountry($faker->country);
            $address->setAddress($faker->streetAddress);
            $address->setLongitude($faker->longitude(-99.99999999, 99.99999999));
            $address->setLatitude($faker->latitude(-99.99999999, 99.99999999));
            $manager->persist($address);

            $company = new Company();
            $company->setDenomination($faker->company);
            $company->setSiren($faker->numerify('#########'));
            $company->setSiret($faker->numerify('##############'));
            $company->setTvaNumber($faker->numerify('FR###########'));
            $company->setRcsNumber($faker->numerify('RCS########'));
            $company->setPhoneNumber($faker->phoneNumber);
            $company->setMail($faker->companyEmail);
            $company->setCreationDate($faker->dateTimeBetween('-10 years', 'now'));
            $company->setRegisteredSocial($faker->numberBetween(1, 1000));
            $company->setSector($industries[array_rand($industries)]);
            $company->setLogo($faker->imageUrl(200, 200, 'business'));
            $company->setSigning($faker->imageUrl(200, 200, 'signature'));
            $company->setLegalForm($legalForms[array_rand($legalForms)]);
            $company->setAddress($address);
            $company->setUid(Uuid::v7());
            $manager->persist($company);
            $companies[] = $company;
        }

        // Create Services
        foreach ($companies as $company) {
            for ($i = 0; $i < 10; $i++) {
                $service = new Service();
                $service->setName($faker->word);
                $service->setDescription($faker->sentence);
                $service->setPrice($faker->randomFloat(2, 10, 1000));
                $service->setEstimatedDuration($faker->numberBetween(1, 8) . ' hours');
                $service->setCompany($company);
                $service->setServiceStatus($serviceStatuses[array_rand($serviceStatuses)]);
                $manager->persist($service);
            }
        }

        // Create Subscriptions and Options
        // Freemium Subscription
        $freemium = new Subscription();
        $freemium->setName('Freemium');
        $freemium->setPrice('0.00');
        $freemium->setDuration(30); // 30 days for example

        $optionsFreemium = [
            ['name' => 'Création et envoi de facture (10 par mois)', 'isActive' => true],
            ['name' => 'Création et envoi de devis (10 par mois)', 'isActive' => true],
            ['name' => 'Ajout d\'autres utilisateurs', 'isActive' => true],
            ['name' => 'Ajout des clients (5 par mois)', 'isActive' => true],
            ['name' => 'Personnalisation du shart graphique', 'isActive' => false],
            ['name' => 'Relance automatique des factures', 'isActive' => false],
            ['name' => 'Personnalisation des rapports', 'isActive' => false],
            ['name' => 'Exporter les données en csv', 'isActive' => false]
        ];

        foreach ($optionsFreemium as $optionData) {
            $option = new SubscriptionOption();
            $option->setName($optionData['name']);
            $option->setIsActive($optionData['isActive']);
            $freemium->addOption($option);
            $manager->persist($option);
        }

        $manager->persist($freemium);

        // Starter Subscription
        $starter = new Subscription();
        $starter->setName('Starter');
        $starter->setPrice('9.99');
        $starter->setDuration(30); // 30 days for example

        $optionsStarter = [
            ['name' => 'Création et envoi de facture (illimité)', 'isActive' => true],
            ['name' => 'Création et envoi de devis (illimité)', 'isActive' => true],
            ['name' => 'Ajout d\'autres utilisateurs', 'isActive' => true],
            ['name' => 'Personnalisation du dashboard', 'isActive' => true],
            ['name' => 'Support client premium', 'isActive' => true],
            ['name' => 'Export de données en format CSV', 'isActive' => true],
            ['name' => 'Ajout des clients (illimité)', 'isActive' => true],
            ['name' => 'Rapports avancés', 'isActive' => false]
        ];

        foreach ($optionsStarter as $optionData) {
            $option = new SubscriptionOption();
            $option->setName($optionData['name']);
            $option->setIsActive($optionData['isActive']);
            $starter->addOption($option);
            $manager->persist($option);
        }

        $manager->persist($starter);

        // Create Articles
        $articleTitles = [
            'How Technology is Shaping the Future',
            'The Impact of Healthcare Advancements',
            'Finance Trends to Watch in 2024',
            'The Evolution of Education in the Digital Age',
            'Manufacturing Innovations and Challenges',
            'Retail: Adapting to a Changing Market',
            'Construction Industry: New Techniques',
            'Transportation: The Road Ahead',
            'Real Estate Market Insights',
            'Energy Sector: Sustainable Solutions'
        ];

        for ($i = 0; $i < 10; $i++) {
            $article = new Article();
            $article->setTitle($articleTitles[$i]);
            $article->setContent($faker->paragraphs(3, true));
            $article->setCreatedAt($faker->dateTimeBetween('-1 years', 'now'));
            $article->setUpdatedAt($faker->dateTimeBetween($article->getCreatedAt(), 'now'));
            $article->setThumbnail($faker->imageUrl(640, 480, 'abstract'));

            $manager->persist($article);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['subscription'];
    }
}
