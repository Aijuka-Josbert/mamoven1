-- Run this once against your database (phpMyAdmin, or `mysql -u root mamaove < this_file.sql`).
-- Adds payment tracking columns needed for the PesaJet mobile money integration.
-- Safe to run on the existing orders table — all new columns are nullable /
-- have sane defaults, so existing rows are unaffected.

ALTER TABLE `orders`
    ADD COLUMN IF NOT EXISTS `payment_method` VARCHAR(20) NOT NULL DEFAULT 'cash_on_delivery' AFTER `status`,
    ADD COLUMN IF NOT EXISTS `payment_status` VARCHAR(20) NOT NULL DEFAULT 'not_applicable' AFTER `payment_method`,
    ADD COLUMN IF NOT EXISTS `payment_reference` VARCHAR(100) DEFAULT NULL AFTER `payment_status`,
    ADD COLUMN IF NOT EXISTS `payment_provider` VARCHAR(20) DEFAULT NULL AFTER `payment_reference`,
    ADD KEY IF NOT EXISTS `idx_orders_payment_reference` (`payment_reference`);

-- payment_method:   'cash_on_delivery' | 'mobile_money'
-- payment_status:   'not_applicable' (COD orders) | 'pending' | 'processing' | 'completed' | 'failed'
-- payment_reference: PesaJet's transactionId, used to poll status later
-- payment_provider:  'mtn' | 'airtel' | 'sandbox'
