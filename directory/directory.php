<?php

$conn = new mysqli( 'localhost', username, password, database );
if( $conn->connect_error )
	die( 'Connection error' );

$result = $conn->query( 'SELECT name, extension, outboundcid FROM users ORDER BY name ASC' );

if ( $result->num_rows > 0 ) {

	if( empty( $_GET[ 'internal' ] ) ) {
		
		header( 'Content-Type: application/xml; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=directory.xml' );
		?>
		<DirectoryIPPhoneDirectory>
			<Title>Directory</Title>

			<?php while( $row = $result->fetch_object() ) { ?>

				<DirectoryEntry>
					<Name><?php echo $row->name; ?></Name>
					<Telephone><?php echo $row->extension; ?></Telephone>
				</DirectoryEntry>

			<?php } ?>
	
		</DirectoryIPPhoneDirectory>

		<?php 
	} else { ?>
		<html>
			<head>
				<title>Phone Directory</title>
				<meta name="viewport" content="width=device-width, initial-scale=1">
			</head>
			<body style="padding: 10px;">
				<style>
					td { padding: 3px; }
				</style>
				<table>
					<tr>
						<th>Name</th>
						<th>Ext</th>
						<th>External #</th>
					</tr>
					<?php while( $row = $result->fetch_object() ) { ?>
						<tr>
							<td><?php echo $row->name; ?></td>
							<td><a href="tel:1207990<?php echo $row->extension; ?>"><?php echo $row->extension; ?></a></td>
							<td><a href="tel:1<?php echo $row->outboundcid; ?>"><?php echo $row->outboundcid; ?></a></td>
						</tr>
					<?php } ?>
				</table>
			</body>
		</html>
			
	<?php }
}
