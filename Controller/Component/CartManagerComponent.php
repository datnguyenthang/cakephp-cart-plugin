<?php
App::uses('Component', 'Controller');
App::uses('CartSessionComponent', 'Cart.Controller/Component');
App::uses('CakeEvent', 'Event');
/**
 * CartManagerComponent
 *
 * This component cares just and only about the cart contents. It will add
 * and remove items from the active cart but nothing more.
 *
 * The component will make sure that the cart content in the sessio and database
 * is always the same and gets merged when a user is not logged in and then logs in.
 *
 * It also can store the cart for non logged in users semi-persistant in a cookie.
 *
 * Checking a cart out and dealing with other stuff is not the purpose of this
 * component.
 *
 * @author Florian Krämer
 * @copyright Florian Krämer
 * @license MIT
 */
class CartManagerComponent extends Component {
/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Auth',
		'Session',
		'Cookie',
		'Cart.CartSession');

/**
 * Id of the active database cart
 *
 * @var string Cart UUID
 */
	protected $_cartId = null;

/**
 * User status if logged in or not
 *
 * @var boolean
 */
	public $_isLoggedIn = false;

/**
 * Default settings
 * - model
 * - buyAction the controller action to use or check for
 * - cartModel
 * - sessionKey
 * - useCookie
 * - cookieName
 * - afterBuyRedirect
 *   - false to disable it
 *   - null to use the referer
 *   - string or array to set a redirect url
 * - getBuy boolean enable/disable capture of buy data via get
 * - postBuy boolean enable/disable capture of buy data via post
 * - update mixed true/false/'get'/'post'
 *   Allow to update the item in cart: true/false for both get and post, or only allow for method
 * - incremental mixed true/false/'get'/'post'
 *   Allow to increment/decrement the item in cart: true/false for both get and post, or only allow for method
 * - remove mixed true/false/'get'/'post'
 *   Allow to remove the item from the cart: true/false for both get and post, or only allow for method
 *
 * @var array
 */
	protected $_defaultSettings = array(
		'model' => null,
		'buyAction' => 'buy',
		'cartModel' => 'Cart.Cart',
		'cartSession' => array('CartSession', 'Cart.Controller/Component'),
		'sessionKey' => 'Cart',
		'useCookie' => false,
		'cookieName' => 'Cart',
		'afterBuyRedirect' => true,
		'afterBuyFailedRedirect' => true,
		'getBuy' => true,
		'postBuy' => true,
		'update' => true,
		'incremental' => true,
		'remove' => true
	);

/**
 * Default settings
 *
 * @var array
 */
	public function initialize(Controller $Controller) {
		$this->settings = array_merge($this->_defaultSettings, $this->settings);
		$this->Controller = $Controller;

		if (empty($this->settings['model'])) {
			$this->settings['model'] = $this->Controller->modelClass;
		}

		$this->sessionKey = $this->settings['sessionKey'];

		$this->_setLoggedInStatus();
		$this->_initalizeCart();
	}

/**
 * Extend the CartManagerComponent and Override this method if needed
 *
 * @return void
 */
	protected function _setLoggedInStatus() {
		if (is_a($this->Controller->Auth, 'AuthComponent') && $this->Controller->Auth->user()) {
			$this->_isLoggedIn = true;
		}
	}

/**
 * Initializes the cart data from session or database depending on if user is logged in or not and if the cart is present or not
 *
 * @todo Do not forget to merge existing itmes with the ones from the database when the user logs in!
 * @return void
 */
	protected function _initalizeCart() {
		extract($this->settings);
		$userId = $this->Auth->user('id');
		$this->CartModel = ClassRegistry::init($cartModel);

		if (!$this->Session->check($sessionKey)) {
			if ($userId) {
				$this->Session->write($sessionKey, $this->CartModel->getActive($userId));
			} else {
				$this->Session->write($sessionKey, array(
					'Cart' => array(),
					'CartsItem' => array()));
			}
		} else {
			if ($userId && !$this->Session->check($sessionKey . '.Cart.id')) {
				$this->Session->write($sessionKey, $this->CartModel->getActive($userId));
			}
		}
		$this->_cartId = $this->Session->read($sessionKey . '.Cart.id');
	}

/**
 * Component startup callback
 *
 * @return void
 */
	public function startup() {
		extract($this->settings);
		if ($this->Controller->action == $buyAction && !method_exists($this->Controller, $buyAction)) {
			$this->captureBuy();
		}
	}

/**
 * Captures a buy from a post or get request
 *
 * @return mixed False if the catpure failed array with item data on success
 */
	public function captureBuy($returnItem = false) {
		extract($this->settings);

		if ($this->Controller->request->is('get')) {
			$data = $this->getBuy();
		}

		if ($this->Controller->request->is('post')) {
			$data = $this->postBuy();
		}
		$type = $this->getType($data);
		if (!$this->typeAllowed($type)) {
			throw new InternalErrorException(__('Type %s is not allowed', $type));
		}

		if (!$data) {
			if ($afterBuyFailedRedirect === true) {
				$this->Session->setFlash(__d('cart', 'Failed to buy the item'));
				$this->Controller->redirect($this->Controller->referer());
			}
			return false;
		}

		$item = $this->updateItem($data);
		if ($returnItem === true) {
			return $item;
		}

		if ($item !== false) {
			if ($this->Controller->request->is('ajax') || $this->Controller->request->is('json') || $this->Controller->request->is('xml')) {
				$this->Controller->set('item', $item);
				$this->Controller->set('_serialize', array('item'));
			} else {
				$this->afterBuyRedirect($item);
			}
		}

		if ($afterBuyFailedRedirect === true) {
			$this->Session->setFlash(__d('cart', 'Failed to buy the item'));
			$this->Controller->redirect($this->Controller->referer());
		}

		return false;
	}

/**
 * Checks if the increment/decrement/update type is allowed.
 *
 * @param string|array $data array or type string
 * @return boolean The type is allowed or not
 */
	public function typeAllowed($type) {
		if (is_array($type)) {
			$type = $this->getType($type);
		}
		if (in_array($type, array('update', 'remove'))) {
			$key = $type;
		} elseif (in_array($type, array('increment', 'decrement'))) {
			$key = 'incremental';
		}

		if (!isset($key)) {
			return false;
		}
		return (
			$this->settings[$key] === true ||
			strtoupper($this->settings[$key]) === $this->Controller->request->method()
		);
	}

/**
 * Returns 'type' from data, if not present defaults to 'update'
 *
 * @param array $data
 * @return string type for the buy action
 */
	public function getType($data) {
		if (!empty($data['CartsItem']['increment'])) {
			return 'increment';
		} elseif (!empty($data['CartsItem']['decrement'])) {
			return 'decrement';
		} elseif (!empty($data['CartsItem']['remove'])) {
			return 'remove';
		}
		return 'update';
	}

/**
 *
 * @param array $data
 */
	public function updateItem($data, $recalculate = true) {
		$type = $this->getType($data);
		$data = $this->_additionalData($data, $type);
		if ($type == 'update' || !$this->contains($data['CartsItem']['foreign_key'], $data['CartsItem']['model'])) {
			return $this->addItem($data, $recalculate);
		}

		$item = $this->getItem($data['CartsItem']['foreign_key'], $data['CartsItem']['model']);
		if ($type == 'increment') {
			$data['CartsItem']['quantity'] += $item['quantity'];
			return $this->addItem($data, $recalculate);
		} elseif ($type == 'decrement') {
			$data['CartsItem']['quantity'] = $item['quantity'] - $data['CartsItem']['quantity'];
			if ($data['CartsItem']['quantity'] > 0) {
				return $this->addItem($data, $recalculate);
			}
			return $this->removeItem($data);
		} elseif ($type == 'remove') {
			return $this->removeItem($data);
		}
		return false;
	}

/**
 * Adds additional data here that depends more or less on the controller and we
 * want to be present in the before
 *
 * @param array $data
 * @return array
 */
	protected function _additionalData($data, $type = 'update') {
		$data['CartsItem']['user_id'] = $this->Auth->user('id');
		$data['CartsItem']['cart_id'] = $this->_cartId;

		if (!isset($data['CartsItem']['model'])) {
			$data['CartsItem']['model'] = $this->settings['model'];
		}
		if (!empty($data['CartsItem'][$type]) && empty($data['CartsItem']['quantity'])) {
			$data['CartsItem']['quantity'] = (int)$data['CartsItem'][$type];
			unset($data['CartsItem'][$type]);
		}

		if (empty($data['CartsItem']['quantity'])) {
			$data['CartsItem']['quantity'] = 1;
		}

		return $data;
	}

/**
 * afterBuyRedirect
 *
 * @param array $item
 * @return vodi
 */
	public function afterBuyRedirect($item) {
		extract($this->settings);
		if ($item === true) {
			$this->Session->setFlash(__d('cart', 'Item was succesfully removed from your cart'));
		} else {
			$this->Session->setFlash(__d('cart', 'You now have %s %s in your cart', $item['quantity'], $item['name']));
		}
		if (is_string($afterBuyRedirect) || is_array($afterBuyRedirect)) {
			$this->Controller->redirect($afterBuyRedirect);
		} elseif ($afterBuyRedirect === true) {
			$this->Controller->redirect($this->Controller->referer());
		}
	}

/**
 * Gets item data via a http get request and url parameters
 *
 * @return mixed false or array
 */
	public function getBuy() {
		if ($this->Controller->request->is('get') && $this->settings['getBuy'] && isset($this->Controller->request->params['named']['item'])) {
			$data = array(
				'CartsItem' => array(
					'foreign_key' => $this->Controller->request->params['named']['item'],
			));

			if (isset($this->Controller->request->params['named']['model'])) {
				$data['CartsItem']['model'] = $this->Controller->request->params['named']['model'];
			}

			if (isset($this->Controller->request->params['named']['quantity'])) {
				$data['CartsItem']['quantity'] = $this->Controller->request->params['named']['quantity'];
			}
			foreach (array('update', 'increment', 'decrement', 'remove') as $type) {
				if (isset($this->Controller->request->params['named'][$type])) {
					$data['CartsItem'][$type] = $this->Controller->request->params['named'][$type];
					break;
				}
			}

			return $data;
		}
		return false;
	}

/**
 * Gets the item data via http post request
 *
 * @return mixed false or array
 */
	public function postBuy() {
		if ($this->Controller->request->is('post') && $this->settings['postBuy']) {
			return $this->Controller->request->data;
		}
		return false;
	}

/**
 * Adds an item to the cart, the session and database if a user is logged in
 *
 * @param array $data
 * @return boolean
 */
	public function addItem($data, $recalculate = true) {
		$data = $this->_additionalData($data);

		CakeEventManager::dispatch(new CakeEvent('CartManager.beforeAddItem', $this, array($data)));

		$Model = ClassRegistry::init($data['CartsItem']['model']);

		if (!$Model->hasMethod('isBuyable') || !$Model->hasMethod('beforeAddToCart')) {
			throw new InternalErrorException(__('The model %s is not implementing isBuyable() or beforeAddToCart() or is not using the BuyableBehavior!', get_class($Model)));
		}

		if (!$Model->isBuyable($data)) {
			// throw exception?
			return false;
		}

		$data = $Model->beforeAddToCart($data);
		if ($data === false || !is_array($data)) {
			return false;
		}

		if ($this->_isLoggedIn) {
			$data = $this->CartModel->addItem($this->_cartId, $data);
			if ($data === false) {
				return false;
			}
			$data = $data['CartsItem'];
		}

		$result = $this->CartSession->addItem($data);
		if ($recalculate) {
			$this->calculateCart();
		}

		CakeEventManager::dispatch(new CakeEvent('CartManager.afterAddItem', $this, array($result)));

		return $result;
	}

/**
 * Removes an item from the cart session and if a user is logged in from the database
 *
 * @param array $data
 * @return boolean
 */
	public function removeItem($data = null) {
		extract($this->settings);

		CakeEventManager::dispatch(new CakeEvent('CartManager.beforeRemoveItem', $this, array($data)));

		if ($this->_isLoggedIn) {
			$this->CartModel->removeItem($this->_cartId, $data['CartsItem']);
		}

		$result = $this->CartSession->removeItem($data['CartsItem']);
		$this->calculateCart();

		CakeEventManager::dispatch(new CakeEvent('CartManager.afterRemoveItem', $this, array($result)));
		return $result;
	}

/**
 * Drops all items and re-initializes the cart
 *
 * @param array $data
 * @return void
 */
	public function emptyCart() {
		if ($this->_isLoggedIn) {
			$this->CartModel->emptyCart($this->_cartId);
		}

		if ($this->settings['useCookie'] == true) {
			$this->Cookie->delete($this->settings['cookieName']);
		}

		$result = $this->CartSession->emptyCart();

		$this->_initalizeCart();
		return $result;
	}

/**
 * Cart content
 *
 * @return array
 */
	public function content() {
		$this->calculateCart();
		return $this->CartSession->read();
	}

/**
 * Find if an item already exists in the cart
 *
 * @param mixed $id integer or string uuid
 * @param string $model Model name
 * @return boolean
 */
	public function contains($id, $model) {
		return $this->getItemKey($id, $model) !== false;
	}

/**
 * Find the item key
 *
 * @param mixed $id integer or string uuid
 * @param string $model Model name
 * @return mixed False or key of the array entry in the cart session
 */
	public function getItemKey($id, $model) {
		return $this->CartSession->getItemKey($id, $model);
	}

	public function getItem($id, $model, $field = '') {
		$key = $this->getItemKey($id, $model);
		if (!$key) {
			return false;
		}

		if (!empty($field)) {
			$field = '.' . $field;
		}

		return $this->CartSession->read('CartsItem.' . $key . $field);
	}

/**
 * Checks if the cart requires shipping
 *
 * @return boolean
 */
	public function requiresShipping() {
		return (bool) $this->Session->read($this->settings['sessionKey'] . '.Cart.requires_shipping');
	}

/**
 * (Re-)calculates a cart, this will run over all items, coupons and taxes
 *
 * @todo refactor saveAll?
 * @return array the cart data
 */
	public function calculateCart() {
		$sessionKey = $this->settings['sessionKey'];
		$cartData = $this->CartModel->calculateCart($this->Session->read($sessionKey));

		if ($this->_isLoggedIn) {
			//$this->CartModel->saveAll($cartData);
		}

		$this->Session->write($sessionKey, $cartData);
		return $cartData;
	}

/**
 * Stores the whole cart in a cookie
 *
 * Use this in the case the user is not logged in to make it semi-persistant
 *
 * @return boolean
 */
	public function writeCookie() {
		return $this->Cookie->write($this->settings['cookieName'], $this->content());
	}

/**
 * Used to restore a cart from a cookie
 *
 * Use this in the case the user was not logged in and left the page for a longer time
 *
 * @return boolean true on success
 */
	public function restoreFromCookie() {
		$result = $this->Cookie->read($this->settings['cookieName']);
		$this->Controller->Session->write($this->settings['sessionKey'], $result);
	}

/**
 * Adds multiple items to the cart, the session and database if a user is logged in
 *
 * @param array $data array of items
 * @param string Buy type, 'update' will replace quantity if present in cart, 'increment' will add to existing quantity
 * @return boolean
 */
	public function updateItems($items, $type = 'update') {
		try {
			$results = array();
			foreach ($items as $item) {
				$item[$type] = true;
				$results[] = $this->updateItem(array('CartsItem' => $item), false);
			}
			$this->calculateCart();
			return !in_array(false, $results, true);
		} catch (Exception $e) {
			throw $e;
		}
	}

/**
 * afterFilter callback
 *
 * @return void
 */
	public function afterFilter() {
		if ($this->settings['useCookie']) {
			$this->writeCookie();
		}
	}

}