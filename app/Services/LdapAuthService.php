<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class LdapAuthService
{
    protected bool $enabled;
    protected string $host;
    protected int $port;
    protected string $baseDn;
    protected string $adminDn;
    protected string $adminPassword;

    public function __construct()
    {
        $this->enabled = config('services.ldap.enabled', false);
        $this->host = config('services.ldap.host', 'ldap.example.com');
        $this->port = config('services.ldap.port', 389);
        $this->baseDn = config('services.ldap.base_dn', 'dc=example,dc=com');
        $this->adminDn = config('services.ldap.admin_dn', '');
        $this->adminPassword = config('services.ldap.admin_password', '');
    }

    /**
     * Authenticate user via LDAP
     */
    public function authenticate(string $username, string $password): ?User
    {
        if (!$this->enabled) {
            return null;
        }

        try {
            $ldapConnection = ldap_connect($this->host, $this->port);
            
            if (!$ldapConnection) {
                Log::error('LDAP connection failed');
                return null;
            }

            ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);

            // Build user DN
            $userDn = "uid={$username},{$this->baseDn}";

            // Try to bind with user credentials
            $bind = @ldap_bind($ldapConnection, $userDn, $password);

            if (!$bind) {
                Log::info('LDAP authentication failed', ['username' => $username]);
                ldap_close($ldapConnection);
                return null;
            }

            // Search for user details
            $userData = $this->searchUser($ldapConnection, $username);

            ldap_close($ldapConnection);

            if ($userData) {
                return $this->syncUser($userData);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('LDAP error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Search user in LDAP
     */
    protected function searchUser($connection, string $username): ?array
    {
        try {
            // Bind with admin to search
            @ldap_bind($connection, $this->adminDn, $this->adminPassword);

            $filter = "(uid={$username})";
            $attributes = ['uid', 'cn', 'mail', 'employeeNumber', 'title', 'department'];
            
            $search = ldap_search($connection, $this->baseDn, $filter, $attributes);
            
            if (!$search) {
                return null;
            }

            $entries = ldap_get_entries($connection, $search);

            if ($entries['count'] > 0) {
                return [
                    'username' => $entries[0]['uid'][0] ?? $username,
                    'name' => $entries[0]['cn'][0] ?? $username,
                    'email' => $entries[0]['mail'][0] ?? null,
                    'nip' => $entries[0]['employeenumber'][0] ?? null,
                    'jabatan' => $entries[0]['title'][0] ?? null,
                    'unit' => $entries[0]['department'][0] ?? null,
                ];
            }

            return null;

        } catch (\Exception $e) {
            Log::error('LDAP search error', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Sync LDAP user to local database
     */
    protected function syncUser(array $ldapData): User
    {
        $user = User::updateOrCreate(
            ['email' => $ldapData['email'] ?? $ldapData['username'] . '@example.com'],
            [
                'name' => $ldapData['name'],
                'password' => bcrypt(\Illuminate\Support\Str::random(32)), // random password, not used
                'ldap_username' => $ldapData['username'],
            ]
        );

        // Update employee if NIP exists
        if ($ldapData['nip'] && $user->employee) {
            $user->employee->update([
                'nip' => $ldapData['nip'],
                'position' => $ldapData['jabatan'] ?? $user->employee->position,
            ]);
        }

        Log::info('User synced from LDAP', ['user_id' => $user->id, 'username' => $ldapData['username']]);

        return $user;
    }

    /**
     * Check if LDAP is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
