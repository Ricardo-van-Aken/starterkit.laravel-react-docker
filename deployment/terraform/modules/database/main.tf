resource "digitalocean_database_cluster" "my_cluster" {
  name       = local.cluster_name
  engine     = var.cluster_engine
  version    = var.cluster_version
  size       = var.cluster_size   # node size
  region     = var.cluster_region # region slug
  node_count = var.cluster_node_count
}

# Create a database inside the cluster (in addition to the default)
resource "digitalocean_database_db" "my_database" {
  cluster_id = digitalocean_database_cluster.my_cluster.id
  name       = local.database_name
}

# Create a user for the database
resource "digitalocean_database_user" "my_user" {
  cluster_id = digitalocean_database_cluster.my_cluster.id
  name       = local.user_name
}

resource "digitalocean_project_resources" "assign_resources" {
  project   = var.project_id
  resources = [digitalocean_database_cluster.my_cluster.urn]
}
