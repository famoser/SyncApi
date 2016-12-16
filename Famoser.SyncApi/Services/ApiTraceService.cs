using Famoser.FrameworkEssentials.Logging;
using Famoser.FrameworkEssentials.Logging.Interfaces;
using Famoser.SyncApi.Services.Interfaces;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace Famoser.SyncApi.Services
{
    public class ApiTraceService : IApiTraceService
    {
        private IExceptionLogger _logger;
        public ApiTraceService()
        {
            _logger = new LogHelper();
        }
        public void LogException(Exception ex, object from = null)
        {
            _logger.LogException(ex, from);
        }

        public void TraceFailedRequest(object request, string link, string message)
        {
            //don't do shit
        }

        public void TraceSuccessfulRequest(object request, string link)
        {
            //don't do shit
        }
    }
}
