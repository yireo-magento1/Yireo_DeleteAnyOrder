# Tip: Be careful when deleting orders
Deleting orders using this module is destructive and irreversible. You need a valid backup before you continue. Also if the financial transaction relating to this order is valid, make sure you have a financial administration that deals with this accordingly.

Make sure you always have a proper backup of your database before removing orders. See our online Delete-Any-Order tutorials for more details.

# Known issues
Note that information on the order is deleted from Magento, but the actual payment or actual shipping is not stopped. Most probably you're using third party extensions for this, which are not touched by this module.

If you're using third party modules that add extra information to an order, those modules will not be cleaned up. However, other modules could observe the event sales_order_delete_after to handle the order-deletion - this is the standard that Magento provides.

There are some third party Magento modules that modify the URL-system of the Magento backend. Because of this, the Delete-Any-Order module (and other modules) are accessed through wrong URLs which results in 404-errors. If you find such a module, please give us this feedback so we can either try to create a workaround in Delete-Any-Order or we contact the third party manufacturor to fix the real issue.

Note that if you have larger quantities of orders, you will need to increase the PHP memory_limit. Values of 2Gb for enterprise shops are not uncommon.

# Magento 2 extension available
Please note that this extension is only available under Magento 1 (which is by now a deprecated platform that you should move away from). We have released a new extension for Magento 2, which is listed on our extensions page.
