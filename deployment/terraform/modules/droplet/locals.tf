module "name_generator" {
  source = ""../../utils/common/name_generator""
  name   = var.name
}

locals {
  name = module.name_generator.result
}
