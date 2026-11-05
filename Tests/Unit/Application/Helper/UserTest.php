<?php


namespace Mollie\Payment\Tests\Unit\Application\Helper;


use Mollie\Payment\Application\Helper\Payment;
use Mollie\Payment\Application\Helper\User;
use OxidEsales\Eshop\Application\Model\Country;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

class UserTest extends UnitTestCase
{
    public function tearDown(): void
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxstates WHERE oxid = "unitTestState"');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxuser WHERE oxid = "unitTestUser"');
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxuser WHERE oxid = "unitApplePayTestUser"');

        parent::tearDown();
    }

    public function testGetStateFromAdministrativeArea()
    {
        $expected = "unitTestState";

        $title = "UnitTestState Title";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('INSERT INTO oxstates (oxid, oxtitle) VALUES ("unitTestState", ?)', array($title));

        $oUser = User::getInstance();
        $result = $oUser->getStateFromAdministrativeArea($title);

        $this->assertEquals($expected, $result);

        $result = $oUser->getStateFromAdministrativeArea("");
        $this->assertFalse($result);
    }

    public function testGetSalByFirstname()
    {
        $oUser = User::getInstance();
        $result = $oUser->getSalByFirstname("TestName - not existant");
        $this->assertFalse($result);

        $expected = "test";
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('INSERT INTO oxuser (oxid, oxusername, oxfname, oxsal) VALUES ("unitTestUser", "unittestuser-sal@example.com", "TestName - existant", ?)', array($expected));

        $result = $oUser->getSalByFirstname("TestName - existant");
        $this->assertEquals($expected, $result);
    }

    public function testGetApplePayUser()
    {
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('INSERT INTO oxuser (oxid, oxusername, oxfname) VALUES ("unitApplePayTestUser", "applepaytest@mollie.com", "TestName - existant")');

        $aBillingContact = [
            'emailAddress' => 'applepaytest@mollie.com',
            'givenName' => 'Paul',
            'familyName' => 'Testwell',
            'addressLines' => ['Teststr. 11'],
            'locality' => 'Testcity',
            'countryCode' => 'TE',
            'postalCode' => '12345',
        ];

        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturn($aBillingContact);

        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('getIdByCode')->willReturn('countryTestId');

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oUser = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('load')->willReturn(true);

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        $oUserHelper = User::getInstance();
        $result = $oUserHelper->getApplePayUser(true);

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\User::class, $result);
    }

    public function testGetApplePayUserDummy()
    {
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnMap([
            ['countryCode', null, 'TE'],
            ['city', null, 'Testcity'],
            ['zip', null, '12345'],
            ['shippingContact', null, ['emailAddress' => 'applepaytest@mollie.com']],
        ]);
        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oView = $this->getMockBuilder(\OxidEsales\Eshop\Application\Controller\FrontendController::class)->disableOriginalConstructor()->getMock();
        $oView->method('getUser')->willReturn(false);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getActiveView')->willReturn($oView);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oCountry = $this->getMockBuilder(Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('getIdByCode')->willReturn('countryTestId');

        UtilsObject::setClassInstance(Country::class, $oCountry);

        $oUser = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('load')->willReturn(true);

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        $oUserHelper = User::getInstance();
        $result = $oUserHelper->getApplePayUser();

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\User::class, $result);
    }

    public function testGetApplePayEmailAddressFalse()
    {
        $oUserHelper = oxNew($this->getProxyClassName(User::class));
        $result = $oUserHelper->getApplePayEmailAddress();

        $this->assertFalse($result);
    }

    public function testGetDummyUserNoCountry()
    {
        $oCountry = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\Country::class)->disableOriginalConstructor()->getMock();
        $oCountry->method('load')->willReturn(true);
        $oCountry->method('getId')->willReturn('countryId');

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\Country::class, $oCountry);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getConfigParam')->willReturn(['countryId']);

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oUser = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)->disableOriginalConstructor()->getMock();
        $oUser->method('load')->willReturn(true);

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Application\Model\User::class, $oUser);

        $oUserHelper = oxNew($this->getProxyClassName(User::class));
        $result = $oUserHelper->getDummyUser();

        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\User::class, $result);
    }

    /**
     * Prepares request, active view and database for the account tests
     *
     * @param  string     $sEmail
     * @param  bool|array $aExistingAccount false = no account, otherwise [oxpassword, oxrights]
     * @param  bool       $blUserActive
     * @return \OxidEsales\Eshop\Application\Model\FrontendController
     */
    protected function prepareApplePayAccountTest($sEmail, $aExistingAccount, $blUserActive = false)
    {
        UtilsObject::resetClassInstances();
        User::destroyInstance();

        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $oDb->execute('DELETE FROM oxuser WHERE oxusername = ?', [$sEmail]);
        if ($aExistingAccount !== false) {
            $oDb->execute(
                'INSERT INTO oxuser (oxid, oxshopid, oxusername, oxpassword, oxrights, oxfname) VALUES ("unitApplePayTestUser", ?, ?, ?, ?, "Existing")',
                [\OxidEsales\Eshop\Core\Registry::getConfig()->getShopId(), $sEmail, $aExistingAccount[0], $aExistingAccount[1]]
            );
        }

        $aParams = ['shippingContact' => ['emailAddress' => $sEmail]];
        $oRequest = $this->getMockBuilder(\OxidEsales\Eshop\Core\Request::class)->disableOriginalConstructor()->getMock();
        $oRequest->method('getRequestEscapedParameter')->willReturnCallback(function ($sName) use ($aParams) {
            return isset($aParams[$sName]) ? $aParams[$sName] : null;
        });
        Registry::set(\OxidEsales\Eshop\Core\Request::class, $oRequest);

        $oActiveUser = false;
        if ($blUserActive === true) {
            $oActiveUser = oxNew(\OxidEsales\Eshop\Application\Model\User::class);
            $oActiveUser->setId('activeTestUser');
        }
        $oView = $this->getMockBuilder(\OxidEsales\Eshop\Application\Controller\FrontendController::class)->disableOriginalConstructor()->getMock();
        $oView->method('getUser')->willReturn($oActiveUser);
        \OxidEsales\Eshop\Core\Registry::getConfig()->setActiveView($oView);

        return $oView;
    }

    protected function cleanupApplePayAccountTest($sEmail)
    {
        \OxidEsales\Eshop\Core\Registry::getConfig()->dropLastActiveView();
        \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->execute('DELETE FROM oxuser WHERE oxusername = ?', [$sEmail]);
        User::destroyInstance();
    }

    public function testGetApplePayUserDoesNotUseCustomerAccount()
    {
        $sEmail = 'applepay-customer@unittest.de';
        $this->prepareApplePayAccountTest($sEmail, ['hashedPassword', 'user']);

        $result = User::getInstance()->getApplePayUser();

        $this->assertNotEquals('unitApplePayTestUser', $result->getId());
        // no second user with the same username is created
        $this->assertEquals(1, \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne('SELECT COUNT(*) FROM oxuser WHERE oxusername = ?', [$sEmail]));

        $this->cleanupApplePayAccountTest($sEmail);
    }

    public function testGetApplePayUserDoesNotUseAdminAccount()
    {
        $sEmail = 'applepay-admin@unittest.de';
        $this->prepareApplePayAccountTest($sEmail, ['', 'malladmin']);

        $result = User::getInstance()->getApplePayUser();

        $this->assertNotEquals('unitApplePayTestUser', $result->getId());

        $this->cleanupApplePayAccountTest($sEmail);
    }

    public function testGetApplePayUserUsesGuestAccount()
    {
        $sEmail = 'applepay-guest@unittest.de';
        $this->prepareApplePayAccountTest($sEmail, ['', 'user']);

        $result = User::getInstance()->getApplePayUser();

        $this->assertEquals('unitApplePayTestUser', $result->getId());

        $this->cleanupApplePayAccountTest($sEmail);
    }

    public function testGetApplePayUserDeliveryMethodCallDoesNotSaveUser()
    {
        $sEmail = 'applepay-new@unittest.de';
        $this->prepareApplePayAccountTest($sEmail, false);

        $result = User::getInstance()->getApplePayUser(true);
        $this->assertInstanceOf(\OxidEsales\Eshop\Application\Model\User::class, $result);
        $this->assertEquals(0, \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne('SELECT COUNT(*) FROM oxuser WHERE oxusername = ?', [$sEmail]));

        // user is saved when the order is created
        User::destroyInstance();
        User::getInstance()->getApplePayUser(false);
        $this->assertEquals(1, \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne('SELECT COUNT(*) FROM oxuser WHERE oxusername = ?', [$sEmail]));

        $this->cleanupApplePayAccountTest($sEmail);
    }

    public function testIsLoginNeededForApplePayEmail()
    {
        $sEmail = 'applepay-login@unittest.de';

        $this->prepareApplePayAccountTest($sEmail, ['hashedPassword', 'user']);
        $this->assertTrue(User::getInstance()->isLoginNeededForApplePayEmail());
        $this->cleanupApplePayAccountTest($sEmail);

        $this->prepareApplePayAccountTest($sEmail, ['', 'malladmin']);
        $this->assertTrue(User::getInstance()->isLoginNeededForApplePayEmail());
        $this->cleanupApplePayAccountTest($sEmail);

        $this->prepareApplePayAccountTest($sEmail, ['', 'user']);
        $this->assertFalse(User::getInstance()->isLoginNeededForApplePayEmail());
        $this->cleanupApplePayAccountTest($sEmail);

        $this->prepareApplePayAccountTest($sEmail, false);
        $this->assertFalse(User::getInstance()->isLoginNeededForApplePayEmail());
        $this->cleanupApplePayAccountTest($sEmail);

        // logged in user: own account is used, no login needed
        $this->prepareApplePayAccountTest($sEmail, ['hashedPassword', 'user'], true);
        $this->assertFalse(User::getInstance()->isLoginNeededForApplePayEmail());
        $this->cleanupApplePayAccountTest($sEmail);
    }

    public function testCreateMollieUser()
    {
        $oCustomers = $this->getMockBuilder(\Mollie\Api\Resources\Customer::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $oCustomers->id = "test";
        $oCustomers->method('create')->willReturn($oCustomers);

        $oConfig = $this->getMockBuilder(\OxidEsales\Eshop\Core\Config::class)->disableOriginalConstructor()->getMock();
        $oConfig->method('getShopConfVar')->willReturn("token");

        Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $oModule = $this->getMockBuilder(\OxidEsales\Eshop\Core\Module\Module::class)->disableOriginalConstructor()->getMock();
        $oModule->method('getInfo')->willReturn('1.2.3');

        UtilsObject::setClassInstance(\OxidEsales\Eshop\Core\Module\Module::class, $oModule);

        $oMollieApi = $this->getMockBuilder(\Mollie\Api\MollieApiClient::class)->disableOriginalConstructor()->getMock();
        $oMollieApi->customers = $oCustomers;

        UtilsObject::setClassInstance(\Mollie\Api\MollieApiClient::class, $oMollieApi);

        $oUser = $this->getMockBuilder(\OxidEsales\Eshop\Application\Model\User::class)->disableOriginalConstructor()->getMock();

        $oUserHelper = User::getInstance();
        $result = $oUserHelper->createMollieUser($oUser);

        $this->assertNull($result);
    }
}