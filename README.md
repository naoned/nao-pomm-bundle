#naoPommBundle

This bundle provides an implementation of [Pomm](http://www.pomm-project.org/) for Symfony.
It extends the [Pomm2 Bundle](https://github.com/pomm-project/pomm-bundle) in order to add some usefull features for developpers.
Pomm components are in version 2


## Installation

Add project to your composer.json

    "repositories": [
        {
            "type": "git",
            "url": "git@gitlab.com:naoned/nao-pomm-bundle.git"
        }
    ],
    "require": {
        ...
        "naoned/nao-pomm-bundle": "@dev"
    },
    ....
    
Add the pomm-bundle to your AppKernel.php
    
    <?php // app/AppKernel.php
    
        public function registerBundles()
        {
            
            $bundles = array(
                ....
                new Naoned\PommBundle\NaoPommBundle(),
            );

## Configuration

See @ [https://github.com/pomm-project/pomm-bundle#configuration](https://github.com/pomm-project/pomm-bundle#configuration)


## Data Fixtures

Inspired by [DoctrineFixturesBundle](http://symfony.com/doc/current/bundles/DoctrineFixturesBundle/index.html), this implementation use Pomm to populate/insert data.

In example we want to create data for the table "Customer" in schema "Account"

    CREATE TABLE customer (
        customer_id uuid DEFAULT public.uuid_generate_v4() NOT NULL,
        name character varying NOT NULL,
        url character varying NULL,
        language character varying DEFAULT ''::character varying NOT NULL
    );

First create a yaml file app/schema/populate/populate.yml
 
    // app/schema/populate/populate.yml
    
    # ================================
    # Populate customer
    # ================================
    Customer:
        name:      "My Customer"
        url:       "http://..."
        language:  "fr"
    
Create the class to define the populate method

    // src/AppBundle/DataFixtures/Pomm/Account/LoadCustomerData.php
    
    <?php
    
    namespace AppBundle\DataFixtures\Pomm\Account;
    
    use Naoned\PommBundle\DataFixtures\PopulatePommModel;
    use AppBundle\Model\ConnexionDb\AccountSchema\CustomerModel;
    
    class LoadCustomerData extends PopulatePommModel
    {
        /**
         * Load data fixtures
         *
         * @param ContainerInterface $container
         */
        public function load()
        {
            /* @var $customer FlexibleEntity
            $customer = $this->populate(CustomerModel::class, 'Customer');
            // Reference are used to store and share data between multiple classes
            // Here we store customer id
            $this->addReference('customer', $customer->getCustomerId());
        }
    
        /**
         * Erase data
         *
         * @param ContainerInterface $container
         */
        public function erase()
        {
            $this->executePommQuery(
                'TRUNCATE account.customer CASCADE'
            );
        }
    
        /**
         * Order is usefull to organize several loading in particular order
         *
         * @return integer
         */
        public function getOrder()
        {
            return 1;
        }
    }


Load fixtures with app/console command

    php app/console naoned:pomm:schema:dataload [-append]

Without "append" option, the command will erase data in DB with erase() function.
    

Other more complicated example with list of data and foreign key. Departments are linked by foreign key with the customer id.
    
    // app/schema/populate/populate.yml
    
    Department:
        - ref: 1
          name: "Department 1"
        - ref: 2
          name: "Department 1"

LoadCustomerData class :

    // src/AppBundle/DataFixtures/Pomm/Account/LoadCustomerData.php
    
    ...
        public function load()
        {
            $model = $this->getPomm()->getModel(DepartmentModel::class);
    
            $departments = $this->get('Department');
            foreach ($departments as $dep) {
                // We retrieve customer id from reference storage
                $dep['customer_id'] = $this->getReference('customer');
                // Save if Pomm
                $department = $model->createAndSave($dep);
                $this->addReference('department_' . $dep['ref'], $department->getDepartmentId());
            }
        }
    ...
        public function getOrder()
        {
            return 2;
        }
    ...

## Schema generation in a sql file

    > php app/console naopomm:schema:generate

This command generates a backup file of your skeleton database. File is located in app/schema/schema.sql


## Creation schemas form schema.sql

    > php app/console naopomm:schema:create

This command create the schema in our DB using the backup schema file.


## PommManager

The PommManager is a simply way to get Model with default session. It's not very different as [pomm-model implementations](https://github.com/pomm-project/pomm-bundle#using-pomm-from-the-controller)

    class MyController
    {
        
        public function testAction($id)
        {
            // Default session
            $this->get('naoned.pomm.pomm_manager')
                ->getModel(UserAccountModel::class)
                ->findByPk(['id' => $id]);

            // Another session
            $this->get('naoned.pomm.pomm_manager')
                ->getModel(UserAccountModel::class, 'My-other-session')
                ->findByPk(['id' => $id]);
                
            // ModelLayer
            $this->get('naoned.pomm.pomm_manager')
                ->getModelLayer(UserAccountModelLayer::class)
                ->myFunction($id);
        }
    }

## PommManagerAware

As the ContainerAware component, PommManagerAware is a quick way to get PommManager from your own services.

    class MyManager extends PommManagerAware
    {
        public function getCustomer($name)
        {
            return $this->pommManager
                ->getModel(CustomerModel::class)
                ->findWhere('name = $*', [$name])
            ;
        }
    }
    


## pomm-bundle capabilities

see @ [https://github.com/pomm-project/pomm-bundle](https://github.com/pomm-project/pomm-bundle)
