<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Prevent UPDATE on protected roles
        DB::unprepared("
            CREATE TRIGGER prevent_protected_role_update
                BEFORE UPDATE ON roles
                FOR EACH ROW
                BEGIN
                IF OLD.is_protected = 1 THEN
                    -- prevent update on name, guard_name, is_active
                    IF NEW.name <> OLD.name 
                    OR NEW.guard_name <> OLD.guard_name 
                    OR NEW.is_active <> OLD.is_active THEN
                    OR NEW.deleted_at <> OLD.deleted_at THEN
                    SIGNAL SQLSTATE '45000'
                        SET MESSAGE_TEXT = 'You cannot update a protected role.';
                    END IF;

                    IF NEW.is_protected <> OLD.is_protected THEN
                        SIGNAL SQLSTATE '45000'
                            SET MESSAGE_TEXT = 'You cannot change is_protected column.';

                END IF;
            END;

        ");

        // Prevent DELETE on protected roles
        DB::unprepared("
            CREATE TRIGGER prevent_delete_protected_roles
            BEFORE DELETE ON roles
            FOR EACH ROW
            BEGIN
                IF OLD.is_protected = 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'You cannot delete a protected role.';
                END IF;
            END
        ");

        // Prevent UPDATE on protected users (allow only fcm_token + remember_token)
        DB::unprepared("
            CREATE TRIGGER prevent_update_protected_users
            BEFORE UPDATE ON users
            FOR EACH ROW
            BEGIN
                IF OLD.is_protected = 1 THEN
                    -- allow only fcm_token and remember_token to change
                    IF NEW.fcm_token <> OLD.fcm_token 
                    OR NEW.remember_token <> OLD.remember_token THEN
                        -- check if any other column changed
                        IF NEW.email <> OLD.email
                        OR NEW.phone_no <> OLD.phone_no
                        OR NEW.password <> OLD.password
                        OR NEW.is_active <> OLD.is_active
                        OR NEW.is_author <> OLD.is_author
                        OR NEW.country_id <> OLD.country_id
                        OR NEW.oauth_type <> OLD.oauth_type
                        OR NEW.email_verified_at <> OLD.email_verified_at
                        OR NEW.phone_verified_at <> OLD.phone_verified_at
                        OR NEW.deleted_at <> OLD.deleted_at
                        THEN
                            SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'You cannot update protected user except fcm_token and remember_token.';
                        END IF;

                        IF NEW.is_protected <> OLD.is_protected THEN
                            SIGNAL SQLSTATE '45000'
                                SET MESSAGE_TEXT = 'You cannot change is_protected column.';
        
                    END IF;
                END IF;
            END
        ");
        
        // Prevent DELETE on protected users
        DB::unprepared("
            CREATE TRIGGER prevent_delete_protected_users
            BEFORE DELETE ON users
            FOR EACH ROW
            BEGIN
                IF OLD.is_protected = 1 THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'You cannot delete a protected user.';
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_update_protected_roles");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_delete_protected_roles");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_update_protected_users");
        DB::unprepared("DROP TRIGGER IF EXISTS prevent_delete_protected_users");
    }
};
