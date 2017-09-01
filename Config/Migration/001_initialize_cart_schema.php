<?php
class D287dbf03fef11e1b86c0800200c9a66 extends CakeMigration {
/**
 * Dependency array. Define what minimum version required for other part of db schema
 *
 * Migration defined like 'app.31' or 'plugin.PluginName.12'
 *
 * @var array $dependendOf
 * @access public
 */
	public $dependendOf = array();

/**
 * Migration array
 *
 * @var array $migration
 * @access public
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'carts' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'name' => array('type' => 'string', 'null' => true, 'default' => null),
					'total' => array('type' => 'float', 'null' => true, 'default' => null),
					'active' => array('type' => 'boolean', 'null' => true, 'default' => '0'),
                    'additional_data' => array('type' => 'text', 'null' => true, 'default' => null, 'comment' => 'For serialized data'),
					'item_count' => array('type' => 'integer', 'null' => false, 'default' => 0, 'length' => 6),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1)
					)
				),
				'order_addresses' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'order_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'first_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'last_name' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'company' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'street' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'street2' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'city' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
					'zip' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 128),
                    'state' => array('type' => 'string', 'null' => true, 'default' => null, 'lenght' => 2),
					'country' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 2),
					'type' => array('type' => 'string', 'null' => true, 'default' => null, 'lenght' => 16),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'USER_INDEX' => array('column' => 'user_id'),
						'ORDER_INDEX' => array('column' => 'order_id')
					)
				),
				'carts_items' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'cart_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'foreign_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'model' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
					'quantity' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4),
                    'quantity_limit' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
                    'additional_data' => array('type' => 'text', 'null' => true, 'default' => null, 'comment' => 'For serialized data'),
					'name' => array('type' => 'string', 'null' => true, 'default' => null),
					'price' => array('type' => 'float', 'null' => true, 'default' => null),
					'virtual' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'Virtual as a download or a service'),
					'status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'comment' => 'internal status, up to the app'), // shipped, delivered, returned, refunded...
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1)
					)
				),
				'orders' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'user_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'cart_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'cart_snapshot' => array('type' => 'text', 'null' => true, 'default' => null),
					'token' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 32),
					'processor' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
                    'order_item_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 8),
					'order_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
					'status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'comment' => 'internal status, up to the app'), // completed, refunded, partial-refund, cancelled, shipped
					'payment_reference' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'status of the transaction'),
					'payment_status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'status of the transaction'),
					'transaction_fee' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => 6,2),
					'invoice_number' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
					'billing_address' => array('type' => 'text', 'null' => true, 'default' => null),
					'shipping_address' => array('type' => 'text', 'null' => true, 'default' => null),
                    'shipping_address_id' => array('type' => 'string', 'null' => true, 'default' => null, 'lenght' => 36),
					'billing_address_id' => array('type' => 'string', 'null' => true, 'default' => null, 'lenght' => 36),
					'shipping_rate' => array('float' => 'string', 'null' => false, 'default' => 0.00),
					'gross' => array('type' => 'float', 'null' => false, 'default' => 0.00),
					'total' => array('type' => 'float', 'null' => true, 'default' => null),
					'currency' => array('type' => 'integer', 'null' => true, 'default' => null),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'USER_INDEX' => array('column' => 'user_id'),
						'CART_INDEX' => array('column' => 'cart_id'),
						'TOKEN_INDEX' => array('column' => 'token')
					)
				),
				'order_items' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'order_id' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'foreign_key' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 36),
					'model' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 64),
					'quantity' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 4),
                    'total' => array('type' => 'float', 'null' => false, 'default' => 0.00, 'length' => 10,2),
                    'additional_data' => array('type' => 'text', 'null' => true, 'default' => null, 'comment' => 'For serialized data'),
					'name' => array('type' => 'string', 'null' => true, 'default' => null),
					'price' => array('type' => 'float', 'null' => true, 'default' => null),
					'virtual' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'Virtual as a download or a service'),
					'status' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 16, 'comment' => 'internal status, up to the app'), // shipped, delivered, returned, refunded...
					'shipped' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'Virtual as a download or a service'),
					'shipping_date' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'FOREIGN_KEY_INDEX' => array('column' => 'foreign_key'),
						'ORDER_INDEX' => array('column' => 'order_id')
					),
				),
				'shipping_methods' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'name' => array('type' => 'string', 'null' => true, 'default' => null),
					'price' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => 6,2),
					'currency' => array('type' => 'integer', 'null' => true, 'default' => null),
					'position' => array('type' => 'integer', 'null' => true, 'default' => null),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1)
					)
				),
				'payment_methods' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'name' => array('type' => 'string', 'null' => true, 'default' => null),
					'alias' => array('type' => 'string', 'null' => true, 'default' => null),
					'class' => array('type' => 'string', 'null' => true, 'default' => null),
					'fee' => array('type' => 'float', 'null' => true, 'default' => null, 'length' => 6,2, 'comment' => 'Can be used to charge a fee for that processor'),
					'active' => array('type' => 'boolean', 'null' => true, 'default' => '0', 'comment' => 'Virtual as a download or a service'),
					'description' => array('type' => 'string', 'null' => true, 'default' => null),
					'position' => array('type' => 'integer', 'null' => true, 'default' => null),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1)
					)
				),
				'payment_api_transactions' => array(
					'id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36, 'key' => 'primary'),
					'order_id' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 36),
					'token' => array('type' => 'string', 'null' => false, 'default' => null),
					'processor' => array('type' => 'string', 'null' => false, 'default' => null),
					'type' => array('type' => 'string', 'null' => false, 'default' => null),
					'message' => array('type' => 'text', 'null' => false, 'default' => null),
					'file' => array('type' => 'text', 'null' => true, 'default' => null),
					'line' => array('type' => 'integer', 'null' => true, 'default' => null, 'length' => 6),
					'trace' => array('type' => 'text', 'null' => true, 'default' => null),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
						'ORDER_INDEX' => array('column' => 'order_id')
					),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'carts', 'carts_items', 'orders', 'order_items', 'shipping_methods', 'order_addresses', 'cart_rules', 'cart_rule_conditions', 'payment_api_transactions', 'payment_methods'),
		)
	);

/**
 * before migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @access public
 */
	public function before($direction) {
		return true;
	}

/**
 * after migration callback
 *
 * @param string $direction, up or down direction of migration process
 * @access public
 */
	public function after($direction) {
		return true;
	}

}
