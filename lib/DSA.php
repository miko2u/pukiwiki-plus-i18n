<?php
/**
 * Security_DSA
 * 
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/bsd-license.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@ishinao.net so we can send you a copy immediately.
 *
 * @category   Security
 * @package   Security
 * @license    http://opensource.org/licenses/bsd-license.php  New BSD License
 * @copyright (c) 2004 Daiji Hriata All Right Reserved.
 * 
 * Author: Daiji Hriata (DSA verify logic in Auth_TypeKey)
 * Author: ishinao <ishinao@ishinao.net> (repackage to Security_DSA)
 * $Id$
 *
 * MODIFICATION
 * 2006-11-19 Katsumi Saito <katsumi@jo1upk.ymt.prug.or.jp>
 * 
 * = how to use =
 * if (Security_DSA::verify($message, $sig, $sigKey)) {
 *   echo 'verify success';
 * } else {
 *   echo 'verify failed';
 * }
 */
class Security_DSA
{
    /**
     * DSA verify
     * 
     * @param string $message message
     * @param string $sig     signature
     * @param array $sigKeys key
     * @return boolean   if success
     * @exception Exception   if extension not exists
     */
    function verify($message, $sig, $sigKeys)
    {
        if (extension_loaded('gmp')) {
            return Security_DSA::_verifyByGmp($message, $sig, $sigKeys);
        } else if (extension_loaded('bcmath')) {
            return Security_DSA::_verifyByBcmath($message, $sig, $sigKeys);
        } else {
            die('gmp or bcmath extension required');
        }
    }

    /**
     * verify using gmp extendsions
     */
    function _verifyByGmp($message, $sig, $sigKeys)
    {
        $p = $sigKeys['p'];
        $q = $sigKeys['q'];
        $g = $sigKeys['g'];
        $pubKey = $sigKeys['pub_key'];

        list ($r_sig, $s_sig) = explode(":", $sig);
        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);

        $p = gmp_init($p);
        $q = gmp_init($q);
        $g = gmp_init($g);
        $pubKey = gmp_init($pubKey);

        $s1 = Security_DSA::_bindecGmp($r_sig);
        $s2 = Security_DSA::_bindecGmp($s_sig);

        $w = gmp_invert($s2, $q);
        $hash_m = gmp_init('0x'.sha1($message));

        $u1 = gmp_mod(gmp_mul($hash_m, $w), $q);
        $u2 = gmp_mod(gmp_mul($s1, $w), $q);

        $v =
            gmp_mod(
                gmp_mod(
                    gmp_mul(
                        gmp_powm($g, $u1, $p),
                        gmp_powm($pubKey, $u2, $p)
                    ),
                $p),
                $q
            );

        return (gmp_cmp($v, $s1) == 0);
    }

    /**
     * binary decode using gmp extension
     */
    function _bindecGmp($bin)
    {
        $dec = gmp_init(0);
        for ($i = 0; $i < strlen($bin); $i ++) {
            $dec = gmp_add(gmp_mul($dec, 256), ord($bin{$i}));
        }
        return $dec;
    }

    /**
     * verify using bcmath extension
     */
    function _verifyByBcmath($message, $sig, $sigKeys)
    {
        $p = $sigKeys['p'];
        $q = $sigKeys['q'];
        $g = $sigKeys['g'];
        $pubKey = $sigKeys['pub_key'];

        list ($r_sig, $s_sig) = explode(':', $sig);

        $r_sig = base64_decode($r_sig);
        $s_sig = base64_decode($s_sig);

        $s1 = Security_DSA::_bindecBcmath($r_sig);
        $s2 = Security_DSA::_bindecBcmath($s_sig);

        $w = Security_DSA::_invertBcmath($s2, $q);
        $hash_m = Security_DSA::_hexdecBcmath(sha1($message));

        $u1 = bcmod(bcmul($hash_m, $w), $q);
        $u2 = bcmod(bcmul($s1, $w), $q);

        $v = 
            bcmod(
                bcmod(
                    bcmul(
                        bcmod(
                            Security_DSA::_powmodBcmath($g, $u1, $p),
                            $p
                        ),
                        bcmod(
                            Security_DSA::_powmodBcmath($pubKey, $u2, $p),
                            $p
                        )
                    ), 
                    $p
                ), 
                $q
            );

        return (bccomp($v, $s1) == 0);
    }

    /**
     * hex decode using bcmath extension
     */
    function _hexdecBcmath($hex)
    {
        $dec = '0';
        for ($i = 0; $i < strlen($hex); $i += 4) {
            $dec = bcadd(bcmul($dec, 65536), HexDec(substr($hex, $i, 4)));
        }
        return $dec;
    }

    /**
     * binary decode using bcmath extension
     */
    function _bindecBcmath($bin)
    {
        $dec = '0';
        for ($i = 0; $i < strlen($bin); $i ++) {
            $dec = bcadd(bcmul($dec, 256), ord($bin{$i}));
        }
        return $dec;
    }

    /**
     * invert using bcmath extension
     */
    function _invertBcmath($x, $y)
    {
        while (bccomp($x, 0) < 0) {
            $x = bcadd($x, $y);
        }
        $r = Security_DSA::_exgcdBcmath($x, $y);
        if ($r[2] == 1) {
            $a = $r[0];
            while (bccomp($a, 0) < 0) {
                $a = bcadd($a, $y);
            }
            return $a;
        } else {
            return false;
        }
    }

    /**
     * exgcd using bcmath extension
     */
    function _exgcdBcmath($x, $y)
    {
        $a0 = 1;
        $a1 = 0;
        $b0 = 0;
        $b1 = 1;
        $c = 0;
        while ($y > 0) {
            $q = bcdiv($x, $y, 0);
            $r = bcmod($x, $y);
            $x = $y;
            $y = $r;
            $a2 = bcsub($a0, bcmul($q, $a1));
            $b2 = bcsub($b0, bcmul($q, $b1));
            $a0 = $a1;
            $a1 = $a2;
            $b0 = $b1;
            $b1 = $b2;
        }
        return array ($a0, $b0, $x);
    }

    /**
     * powmod using bcmath extension
     */
    function _powmodBcmath($x, $y, $mod)
    {
        if (function_exists('bcpowmod')) {
            return bcpowmod($x, $y, $mod);
        } else {
            if (bccomp($y, 1) == 0) {
                return bcmod($x, $mod);
            } else if (bcmod($y, 2) == 0) {
                return bcmod(bcpow(Security_DSA::_powmodBcmath($x, bcdiv($y, 2), $mod), 2), $mod);
            } else {
                return bcmod(bcmul($x, Security_DSA::_powmodBcmath($x, bcsub($y, 1), $mod)), $mod);
            }
        }
    }
}
