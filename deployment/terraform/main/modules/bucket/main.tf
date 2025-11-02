resource "digitalocean_spaces_bucket" "my_bucket" {
  name   = local.name
  region = var.region
  acl    = var.acl
}

resource "digitalocean_spaces_key" "scoped" {
  name = var.key_name

  grant {
    bucket     = digitalocean_spaces_bucket.my_bucket.name
    permission = "readwrite"
  }
}

resource "digitalocean_project_resources" "assign_resources" {
  project   = var.project_id
  resources = [digitalocean_spaces_bucket.my_bucket.urn]
}
