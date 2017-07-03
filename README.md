# WPGraphQL Fieldmanager

WPGraphQL bindings for WordPress Fieldmanager, by Alley Interactive. 

![Example of WPGraphQL Fieldmanager in action](https://github.com/wp-graphql/wp-graphql-fieldmanager/blob/master/img/fieldmanager-graphql.gif?raw=true "WPGraphQL Fieldmanager Gif example")

## Getting Started

This plugin is an extension to both WPGraphQL and WordPress Fieldmanager and requires both of those plugins to be installed
to work properly. 

### Install the plugin

Download or clone the plugin from Github and put in your WordPress plugins directory and activate the plugin.

## Connect Fieldmanager Schema to WPGraphQL

Let's say you have this existing Fieldmanager Config to add some fields to the "post" post_type: 

```
add_action( 'fm_post_post', function() {

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

	$fm->add_meta_box( 'Contact Information', 'post' );

}
```

Simply update the last line to be: 

```
$fm_config = $fm->add_meta_box( 'Contact Information', 'post' );
\WPGraphQL\Extensions\Fieldmanager::add_fields( $fm_config );
```

And now the `post` post_type will have the defined fields in the WPGraphQL Schema. 

**NOTE:** This will add all fields in the configuration to the WPGraphQL Schema. There will likely be per-field `show_in_graphql` support at some point in the future.

## Query Fieldmanager fields

Once your fields are connected to WPGraphQL, they will show in your GraphQL Schema and you can query them. 

Using the examples above, we have 2 fields connected to the root of the "post" type: `demo_field` and `contact_information`, 
where `demo_field` is a single field and `contact_information` is a field group.
 
Now, we can run a query like so: 

```
{
  post(id: "{post_global_id_goes_here}") {
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
