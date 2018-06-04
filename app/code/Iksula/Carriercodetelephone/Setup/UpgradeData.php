<?php

namespace Iksula\Carriercodetelephone\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
/* irrelevant */
#use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
/* irrelevant */
#use Magento\Framework\Setup\SchemaSetupInterface;
/* add this */
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements  UpgradeDataInterface
{
     private $customerSetupFactory;

    public function __construct(\Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }


    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context){
        $setup->startSetup();

            if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'account_telephone',
                [
                'type' => 'varchar',
                'label' => 'Telephone',
                'input' => 'text',
                'required' => false,
                'sort_order' => 100,
                'system' => false,
                'position' => 100
                ]
            );
           $telephoneAttribute =$customerSetup->getEavConfig()->getAttribute('customer', 'account_telephone');
            $telephoneAttribute->setData('used_in_forms', ['adminhtml_customer' , 'customer_account_create' , 'customer_account_edit'])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined",1)
            ->setData("is_visible", 1)
            ->setData("is_used_in_grid", 1)
            ->setData("sort_order", 100)
            ->save();
        }


        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'nationality',
                [
                    'type' => 'text',
                    'label' => 'Nationlity',
                    'input' => 'select',
                    'required' => false,
                    'sort_order' => 100,
                    'system' => false,
                    'position' => 100,
                    'backend' => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
                    'source' => 'Iksula\Carriercodetelephone\Model\Entity\Attribute\Nationality\Options'
                ]
            );
           $nationalityAttribute =$customerSetup->getEavConfig()->getAttribute('customer', 'nationality');
            $nationalityAttribute->setData('used_in_forms', ['adminhtml_customer' , 'customer_account_create' , 'customer_account_edit'])
            ->setData("is_used_for_customer_segment", true)
            ->setData("is_system", 0)
            ->setData("is_user_defined",1)
            ->setData("is_visible", 1)
            ->setData("is_used_in_grid", 1)
            ->setData("sort_order", 100)
            ->save();
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            /** @var CustomerSetup $customerSetup */
            $setup->run("create table directory_area_region
            (area_id int not null auto_increment, region_id int(200) ,  area_name varchar(100) , area_code varchar(100)
              , primary key(area_id))");


              $setup->run("insert into directory_area_region (area_code , area_name , region_id)
                  values
                  ('Al Maqta' , 'Al Maqta' , 512),
                  ('Abu Dhabi Gate City' , 'Abu Dhabi Gate City' , 512),
                  ('Mangrove Village' , 'Mangrove Village' , 512),
                  ('Al Madina Al Riyadiya' , 'Al Madina Al Riyadiya' , 512),
                  ('Sheikh Zayed Grand Mosque' , 'Sheikh Zayed Grand Mosque' , 512),
                  ('Officers Club' , 'Officers Club' , 512),
                  ('Al Matar-AbuDhabi' , 'Al Matar-AbuDhabi' , 512),
                  ('Al Qurm - Abu Dhabi' , 'Al Qurm - Abu Dhabi' , 512),
                  ('Al Maqat Heritage Market' , 'Al Maqat Heritage Market' , 512),
                  ('Al Safarat' , 'Al Safarat' , 512),
                  ('Al Mushrif Mall' , 'Al Mushrif Mall' , 512),
                  ('Al Mushrif' , 'Al Mushrif' , 512),
                  ('Al Bateen Beach' , 'Al Bateen Beach' , 512),
                  ('Al Gurm Resort' , 'Al Gurm Resort' , 512),
                  ('Al hamriya Free Zone' , 'Al hamriya Free Zone' , 512),
                  ('Al hamriyah' , 'Al hamriyah' , 513),
                  ('Nujoom island' , 'Nujoom island' , 513),
                  ('Al hamriya Free Zone 2' , 'Al hamriya Free Zone 2' , 513),
                  ('New Hamariya' , 'New Hamariya' , 513),
                  ('Al Rumailah' , 'Al Rumailah' , 513),
                  ('Al Nakheel - Ajman' , 'Al Nakheel - Ajman' , 513),
                  ('Al Shuwaib' , 'Al Shuwaib' , 514),
                  ('Al Khateem' , 'Al Khateem' , 514),
                  ('Al Towayya' , 'Al Towayya' , 514),
                  ('Al Khabisi' , 'Al Khabisi' , 514),
                  ('Al Jimi' , 'Al Jimi' , 514),
                  ('Al Masoudi' , 'Al Masoudi' , 514),
                  ('Al Muwaiji' , 'Al Muwaiji' , 514),
                  ('Al Hamriya Port' , 'Al Hamriya Port' , 515),
                  ('Al Wuheida' , 'Al Wuheida' , 515),
                  ('Hor Al Anz East' , 'Hor Al Anz East' , 515),
                  ('Al Nahda 1 - Dubai' , 'Al Nahda 1 - Dubai' , 515),
                  ('Al Nahda 2 - Dubai' , 'Al Nahda 2 - Dubai' , 515),
                  ('Al Quasis 1' , 'Al Quasis 1' , 515),
                  ('Al Quasis 2' , 'Al Quasis 2' , 515),
                        ('Al Mailaiha' , 'Al Mailaiha' , 516),
                        ('Sakamkam 1' , 'Sakamkam 1' , 516),
                        ('Sakamkam 2 - Al Hilal City (U/C)' , 'Sakamkam 2 - Al Hilal City (U/C)' , 516),
                        ('Al Faseel' , 'Al Faseel' , 516),
                        ('Al Sharia' , 'Al Sharia' , 516),
                        ('Madhab' , 'Madhab' , 516),
                        ('Old Fujairah City' , 'Old Fujairah City' , 516),
                        ('Rak City' , 'Rak City' , 517),
                        ('Sidroh 1' , 'Sidroh 1' , 517),
                        ('Sidroh 2' , 'Sidroh 2' , 517),
                        ('Dahan' , 'Dahan' , 517),
                        ('Dafan Al Khor' , 'Dafan Al Khor' , 517),
                        ('Al Qurm - RAK' , 'Al Qurm - RAK' , 517),
                        ('Al Turfah' , 'Al Turfah' , 517),
                        ('Al Mamzar' , 'Al Mamzar' , 518),
                        ('Al Khan' , 'Al Khan' , 518),
                        ('Al Tawun - Al Tawun road' , 'Al Tawun - Al Tawun road' , 518),
                        ('Al Majaz 2, Buhaira' , 'Al Majaz 2, Buhaira' , 518),
                        ('Al Majaz 3' , 'Al Majaz 3' , 518),
                        ('Al Khaledia' , 'Al Khaledia' , 518),
                        ('Al Zawrah46' , 'Al Zawrah46' , 519),
                        ('Defence Camp' , 'Defence Camp' , 519),
                        ('Al Humrah - D' , 'Al Humrah - D' , 519),
                        ('Al Humrah - B' , 'Al Humrah - B' , 519),
                        ('Al Humrah - C' , 'Al Humrah - C' , 519),
                        ('Al Humrah - A' , 'Al Humrah - A' , 519)
              ");


              $setup->run("insert into directory_country_region
              (region_id , country_id , code , default_name) values
                  (512, 'AE', 'AE', 'Abu Dhabi'),
                  (513, 'AE', 'AE', 'Ajman'),
                  (514, 'AE', 'AE', 'Al Ain'),
                  (515, 'AE', 'AE', 'Dubai'),
                  (516, 'AE', 'AE', 'Fujairah'),
                  (517, 'AE', 'AE', 'Ras al Khaimah'),
                  (518, 'AE', 'AE', 'Sharjah'),
                  (519, 'AE', 'AE', 'Umm al Quwain')
              ");





        }

        $setup->endSetup();

    }

}
