import supabase from '../_supabase.js'

export default async function handler(req, res) {
  try {
    const match = req.url.match(/\/api\/routes\/(.+)$/)
    const routeId = match ? decodeURIComponent(match[1]) : null
    if (!routeId) return res.status(400).json({ error: 'routeId required' })

    const { data: routes, error } = await supabase
      .from('transit_routes')
      .select('id, name, on_time_rate, stops(*), vehicles(*), notices(*)')
      .eq('id', routeId)
      .limit(1)

    if (error) return res.status(500).json({ error: error.message })
    if (!routes || routes.length === 0) return res.status(404).json({ error: 'not found' })

    const r = routes[0]
    return res.status(200).json(r)
  } catch (err) {
    return res.status(500).json({ error: String(err) })
  }
}
