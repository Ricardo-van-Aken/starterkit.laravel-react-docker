output "result" {
  value = "${terraform.workspace}-${var.name}-${random_string.suffix.result}"
}

output "suffix" {
  value = random_string.suffix.result
}
