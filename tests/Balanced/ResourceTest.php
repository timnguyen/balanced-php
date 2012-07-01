<?php

namespace Balanced\Test;

use Balanced\Core\Resource;
use Balanced\Core\Collection;
use Balanced\Settings;
use Balanced\APIKey;
use Balanced\Marketplace;
use Balanced\Credit;
use Balanced\Debit;
use Balanced\Refund;
use Balanced\Account;
use Balanced\Merchant;
use Balanced\BankAccount;
use Balanced\Card;
use Balanced\Hold;


class APIKeyTest extends \PHPUnit_Framework_TestCase
{

    function testRegistry()
    {
        $this->expectOutputString('');
        $result = Resource::getRegistry()->match('/v1/api_keys');
        return;
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\APIKey',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/api_keys/1234');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\APIKey',
            'ids' => array('id' => '1234'),
            );
        $this->assertEquals($expected, $result);
    }
}

class MarketplaceTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/marketplaces');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Marketplace',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/marketplaces/1122');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Marketplace',
            'ids' => array('id' => '1122'),
            );
        $this->assertEquals($expected, $result);
    }
    
    function testCreateCard()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Card', 'some/uri', null)
            );

        $collection->expects($this->once())
                   ->method('create')
                   ->with(array(
                       'street_address' => '123 Fake Street',
                       'city' => 'Jollywood',
                       'region' => 'CA',
                       'postal_code' => '90210',
                       'name' => 'khalkhalash',
                       'card_number' => '4112344112344113',
                       'expiration_month' => 12,
                       'expiration_year' => 2013,
                       ));
        
        $marketplace = new Marketplace(array('cards' => $collection));
        $marketplace->createCard(
            '123 Fake Street',
            'Jollywood',
            'CA',
            '90210',
            'khalkhalash',
            '4112344112344113',
            12,
            2013);
    }
    
    function testCreateBankAccount()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\BankAccount', 'some/uri', null)
            );
        
        $collection->expects($this->once())
                   ->method('create')
                   ->with(array(
                       'name' => 'Homer Jay',
                       'account_number' => '112233a',
                       'bank_code' => '121042882'
                       ));
        
        $marketplace = new Marketplace(array('bank_accounts' => $collection));
        $marketplace->createBankAccount(
            'Homer Jay',
            '112233a',
            '121042882');
    }
    
    function testCreateBuyer()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Account', 'some/uri', null)
            );
        
        $collection->expects($this->once())
                   ->method('create')
                   ->with(array(
                       'email_address' => 'buyer@example.com',
                       'card_uri' => '/some/card/uri',
                       'meta' => array('test#' => 'test_d')
                       ));
        
        $marketplace = new Marketplace(array('accounts' => $collection));
        $marketplace->createBuyer(
            'buyer@example.com',
            '/some/card/uri',
            array('test#' => 'test_d')
            );
    }
}

class AccountTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/accounts');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Account',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/accounts/0099');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Account',
            'ids' => array('id' => '0099'),
            );
        $this->assertEquals($expected, $result);
    }

    function testCredit()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Credit', 'some/uri', null)
        );
        
        $collection
            ->expects($this->once())
            ->method('create')
            ->with(array(
               'amount' => 101,
               'description' => 'something sweet',
               'meta' => null,
               ));
        
        $account = new Account(array('credits' => $collection));
        $account->credit(101, 'something sweet');
    }
    
    function testDebit()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Debit', 'some/uri', null)
            );
        
        $collection
            ->expects($this->once())
            ->method('create')
            ->with(array(
                'amount' => 9911,
                'description' => 'something tangy',
                'meta' => null,
                'appears_on_statement_as' => 'BAL*TANG',                       
                ));
        
        $account = new Account(array('debits' => $collection));
        $account->debit(9911, 'BAL*TANG', 'something tangy');
    }
    
    function testHold()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Hold', 'some/uri', null)
            );
        
        $collection
            ->expects($this->once())
            ->method('create')
            ->with(array(
                'amount' => 1243,
                'description' => 'something crispy',
                'source_uri' => '/some/card/uri',
                'meta' => array('test#' => 'test_d')
                ));

        $account = new Account(array('holds' => $collection));
        $account->hold(
            1243,
            'something crispy',
            '/some/card/uri',
            array('test#' => 'test_d')
            );
    }
    
    function testAddCard()
    {
        $account = $this->getMock(
            '\Balanced\Account',
            array('save')
            );
        
        $account
            ->expects($this->once())
            ->method('save')
            ->with();
        
        $account->addCard('/my/new/card/121212');
        $this->assertEquals($account->card_uri, '/my/new/card/121212');
    }
    
    function testAddBankAccount()
    {
        $account = $this->getMock(
            '\Balanced\Account',
            array('save')
            );
        
        $account
            ->expects($this->once())
            ->method('save')
            ->with();
        
        $account->addBankAccount('/my/new/bank_account/121212');
        $this->assertEquals($account->bank_account_uri, '/my/new/bank_account/121212');
    }
}

class HoldTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/holds');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Hold',
        );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/holds/112233');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Hold',
            'ids' => array('id' => '112233'),
        );
        $this->assertEquals($expected, $result);
    }
    
    function testVoid()
    {
        $hold = $this->getMock(
            '\Balanced\Hold',
            array('save')
            );
        
        $hold
            ->expects($this->once())
            ->method('save')
            ->with();
        
        $hold->void();
        $this->assertTrue($hold->is_void);
    }
    
    function testCapture()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Debit', 'some/uri', null)
            );
        
        $collection
            ->expects($this->once())
            ->method('create')
            ->with(array(
                'hold_uri' => 'some/hold/uri',
                'amount' => 2211,
                ));

        $account = new Account(array('debits' => $collection));
        
        $hold = new Hold(array('uri' => 'some/hold/uri', 'account' => $account));
        
        $hold->capture(2211);
    }
}

class CreditTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/credits');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Credit',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/credits/9988');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Credit',
            'ids' => array('id' => '9988'),
            );
        $this->assertEquals($expected, $result);
    }
}

class DebitTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/debits');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Debit',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/debits/4545');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Debit',
            'ids' => array('id' => '4545'),
            );
        $this->assertEquals($expected, $result);
    }
    
    function testRefund()
    {
        $collection = $this->getMock(
            '\Balanced\Core\Collection',
            array('create'),
            array('\Balanced\Refund', 'some/uri', null)
        );
        
        $collection
            ->expects($this->once())
            ->method('create')
            ->with(array(
                'amount' => 5645,
                'description' => null,
                'meta' => array('test#' => 'test_d')
                ));
        
        $debit = new Debit(array('refunds' => $collection));
        
        $debit->refund(5645, null, array('test#' => 'test_d'));
    }
}

class RefundTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/refunds');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Refund',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/refunds/1287');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Refund',
            'ids' => array('id' => '1287'),
            );
        $this->assertEquals($expected, $result);
    }
}

class BankAccountTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/bank_accounts');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\BankAccount',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/bank_accounts/887766');
        $expected = array(
                'collection' => false,
                'class' => 'Balanced\BankAccount',
                'ids' => array('id' => '887766'),
        );
        $this->assertEquals($expected, $result);
    }
}

class CardTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/cards');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Card',
            );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/cards/136asd6713');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Card',
            'ids' => array('id' => '136asd6713'),
            );
        $this->assertEquals($expected, $result);
    }
}


class MerchantTest extends \PHPUnit_Framework_TestCase
{
    function testRegistry()
    {
        $result = Resource::getRegistry()->match('/v1/merchants');
        $expected = array(
            'collection' => true,
            'class' => 'Balanced\Merchant',
        );
        $this->assertEquals($expected, $result);
        $result = Resource::getRegistry()->match('/v1/merchants/136asd6713');
        $expected = array(
            'collection' => false,
            'class' => 'Balanced\Merchant',
            'ids' => array('id' => '136asd6713'),
        );
        $this->assertEquals($expected, $result);
    }
}