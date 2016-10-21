using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using Famoser.SyncApi.Models.Interfaces.Base;

namespace Famoser.SyncApi.Models.Base
{
    public abstract class BaseModel : IUniqueSyncModel
    {
        private Guid _userId;
        public Guid GetId()
        {
            return _userId;
        }

        public void SetId(Guid id)
        {
            _userId = id;
        }

        public abstract string GetUniqeIdentifier();
    }
}
