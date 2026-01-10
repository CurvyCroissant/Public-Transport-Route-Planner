import { createClient } from '@supabase/supabase-js'

const url = process.env.SUPABASE_URL
const key = process.env.SUPABASE_SERVICE_ROLE || process.env.SUPABASE_KEY

if (!url || !key) {
  console.warn('SUPABASE_URL or SUPABASE_KEY not set in environment')
}

export const supabase = createClient(url || '', key || '')

export default supabase
