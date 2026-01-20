resource "digitalocean_droplet" "my_droplet" {
  name     = local.name
  region   = var.region
  size     = var.size
  image    = var.image
  ssh_keys = [data.digitalocean_ssh_key.me.id]
}

resource "digitalocean_project_resources" "assign_resources" {
  project   = var.project_id
  resources = [digitalocean_droplet.my_droplet.urn]
}
