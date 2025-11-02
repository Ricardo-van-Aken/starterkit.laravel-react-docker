resource "digitalocean_spaces_key" "admin_key" {
  name = "tf-admin-key"

  grant {
    bucket     = ""
    permission = "fullaccess"
  }
}
