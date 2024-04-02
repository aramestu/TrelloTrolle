<?php

namespace App\Trellotrolle\Test\LibTest;

use App\Trellotrolle\Lib\MotDePasse;
use PHPUnit\Framework\TestCase;

class MotDePasseTest extends TestCase
{

// hacher method returns a string hashed with PASSWORD_BCRYPT algorithm
    public function test_hacher_returns_hashed_string()
    {
        $mdpClair = 'password123';
        $motDePasse = new MotDePasse();
        $hashedString = $motDePasse->hacher($mdpClair);

        var_dump($hashedString);
        $this->assertIsString($hashedString);
        $this->assertTrue(password_verify($mdpClair, $hashedString));
    }

// verifier method returns true if the clear password matches the hashed password, false otherwise
    public function test_verifier_returns_true_if_password_matches()
    {
        $mdpClair = 'password123';
        $motDePasse = new MotDePasse();
        $hashedString = $motDePasse->hacher($mdpClair);

        $this->assertTrue($motDePasse->verifier($mdpClair, $hashedString));
    }

// genererChaineAleatoire method returns a random string of hexadecimal characters
    public function test_genererChaineAleatoire_returns_random_hex_string()
    {
        $motDePasse = new MotDePasse();
        $randomString = $motDePasse->genererChaineAleatoire();

        $this->assertIsString($randomString);
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $randomString);
    }

// hacher method returns a string of length 60
    public function test_hacher_returns_string_of_length_60()
    {
        $mdpClair = 'password123';
        $motDePasse = new MotDePasse();
        $hashedString = $motDePasse->hacher($mdpClair);

        $this->assertEquals(60, strlen($hashedString));
    }

// verifier method returns false if the clear password does not match the hashed password
    public function test_verifier_returns_false_if_password_does_not_match()
    {
        $mdpClair = 'password123';
        $wrongPassword = 'wrongpassword';
        $motDePasse = new MotDePasse();
        $hashedString = $motDePasse->hacher($mdpClair);

        $this->assertFalse($motDePasse->verifier($wrongPassword, $hashedString));
    }

// verifier method returns false if the hashed password is not a valid hash created by password_hash
    public function test_verifier_returns_false_if_hashed_password_is_not_valid()
    {
        $mdpClair = 'password123';
        $motDePasse = new MotDePasse();
        $hashedString = $motDePasse->hacher($mdpClair);

        // Modify the hashed string to make it invalid
        $invalidHashedString = substr_replace($hashedString, 'x', 0, 1);

        $this->assertFalse($motDePasse->verifier($mdpClair, $invalidHashedString));
    }


}
