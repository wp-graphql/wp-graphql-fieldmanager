# WPGraphQL Fieldmanager

WPGraphQL bindings for WordPress Fieldmanager, by Alley Interactive. 

![Example of WPGraphQL Fieldmanager in action](https://github.com/wp-graphql/wp-graphql-fieldmanager/blob/master/img/fieldmanager-graphql.gif?raw=true "WPGraphQL Fieldmanager Gif example")

## Getting Started

This plugin is an extension to both WPGraphQL and WordPress Fieldmanager and requires both of those plugins to be installed
to work properly. 

### Install the plugin

Download or clone the plugin from Github and put in your WordPress plugins directory and activate the plugin.

## Connect Fieldmanager Schema to WPGraphQL

**NOTE:** This will likely change, but for now:

Let's say you have this existing Fieldmanager configuration that adds a 
metabox to the Posts screen, which includes a Field group and various nested
fields. 
```
function get_fieldmanager_post_config() {

	$fm = new Fieldmanager_Group( array(
		'name' => 'contact_information',
		'children' => array(
			'name' => new Fieldmanager_Textfield( 'Name' ),
			'phone' => new Fieldmanager_Textfield( 'Phone Number' ),
			'website' => new Fieldmanager_Link( 'Website' ),
			'sub_group' => new Fieldmanager_Group([
				'name' => 'sub_group',
				'label' => __( 'Sub Group', 'wp-graphql-fieldmanager' ),
				'serialize_data' => false,
				'add_to_prefix'  => false,
				'children' => [
					'sub_group_field' => new Fieldmanager_TextField( 'Sub Group Field' ),
					'another_sub_group' => new Fieldmanager_Group([
						'name' => 'another_sub_group',
						'children' => [
							'another_sub_group_text_field' => new Fieldmanager_TextField( 'Another Sub Group Field' ),
						],
					]),
				],
			]),
		),
	) );

	$fieldmanager_config = $fm->add_meta_box( 'Contact Information', 'post' );
	return $fieldmanager_config;

}

// Add the meta box to the post screen
add_action( 'fm_post_post', function() {
	get_fieldmanager_post_config();
} );
```
To register this Schema to GraphQL, you simply need to do the following:

```
add_filter( 'graphql_fieldmanager_schema', function( $fields ) {
	$fields[] = get_fieldmanager_post_config();
	return $fields;
} );
```

Of course, you likely have additional metaboxes or field configurations, so you can easily register multiple fieldmanager
configurations to GraphQL as well. Let's say you had this additional Fieldmanager
configuration:

```
function get_fieldmanager_post_config_2() {

	$fm = new Fieldmanager_TextField( array(
		'name' => 'demo_field',
		'description' => 'Demo Field for Posts',
	) );
	$fieldmanager_config = $fm->add_meta_box( 'Single Field', 'post' );
	return $fieldmanager_config;

}

// Add the metabox to the post screen
add_action( 'fm_post_post', function() {
	get_fieldmanager_post_config_2();
} );
```

You could simply add that config to the same filter (or another one) to connect it to WPGraphQL like so:

```
add_filter( 'graphql_fieldmanager_schema', function( $fields ) {
	$fields[] = get_fieldmanager_post_config();
	$fields[] = get_fieldmanager_post_config_2();
	return $fields;
} );

```

## Query Fieldmanager fields

Once your fields are connected to WPGraphQL, they will show in your GraphQL Schema and you can query them. 

Using the examples above, we have 2 fields connected to the root of the "post" type: `demo_field` and `contact_information`, 
where `demo_field` is a single field and `contact_information` is a field group.
 
Now, we can run a query like so: 

```
{
  post(id: "cG9zdDoyNDA3") {
    id
    postId
    contactInformation {
      name {
        value
      }
      phone {
        value
      }
      website {
        value
      }
      subGroup{
        subGroupField{
          value
        }
        anotherSubGroup{
          anotherSubGroupTextField{
            value
          }
        }
      }
    }
    demoField {
      value
    }
  }
}
```
